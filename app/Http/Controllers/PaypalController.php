<?php

namespace App\Http\Controllers;

use App\Http\Services\PaypalService;
use App\Models\Beat;
use App\Models\Purchase;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use \PayPal\Api\VerifyWebhookSignature;
use \PayPal\Api\WebhookEvent;

class PaypalController extends Controller
{

    protected PaypalService $paypalService;

    /**
     * @param PaypalService $paypalService
     */
    public function __construct(PaypalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }


    public function handleWebhook(Request $request)
    {
        $requestBody = file_get_contents('php://input');
        $headers = $request->headers->all();
        Log::info('paypal webhook', [
            'requestBody' => $requestBody
        ]);

//check if webhook payload has data
        if (!$requestBody) {
            exit();
        }

        $keys = [
            strtolower('PAYPAL-AUTH-ALGO'),
            strtolower('PAYPAL-TRANSMISSION-ID'),
            strtolower('PAYPAL-CERT-URL'),
            strtolower('PAYPAL-TRANSMISSION-SIG'),
            strtolower('PAYPAL-TRANSMISSION-TIME')
        ];

        foreach ($keys as $key) {
            if(!array_key_exists($key, $headers)) {
                Log::info('missing headers', [
                    'headers' => $headers,
                ]);
                exit();
            }
        }


        $webhookID = config('paypal.webhook_id');

        //start paypal webhook signature validation


        $signatureVerification = new VerifyWebhookSignature();
        $signatureVerification->setAuthAlgo($headers[strtolower('PAYPAL-AUTH-ALGO')][0]);
        $signatureVerification->setTransmissionId($headers[strtolower('PAYPAL-TRANSMISSION-ID')][0]);
        $signatureVerification->setCertUrl($headers[strtolower('PAYPAL-CERT-URL')][0]);
        $signatureVerification->setWebhookId($webhookID);
        $signatureVerification->setTransmissionSig($headers[strtolower('PAYPAL-TRANSMISSION-SIG')][0]);
        $signatureVerification->setTransmissionTime($headers[strtolower('PAYPAL-TRANSMISSION-TIME')][0]);

        $signatureVerification->setRequestBody($requestBody);
        $request = clone $signatureVerification;

        try {

            $output = $signatureVerification->post($this->paypalService->getApiContext());

        } catch (Exception $ex) {

//error during signature validation, capture error and exit

//            ResultPrinter::printError("Validate Received Webhook Event", "WebhookEvent", null, $request->toJSON(), $ex);
            Log::error('error verifying webhook signature', [
                'e' => $ex,
            ]);
            exit(1);

        }

        $sigVerificationResult = $output->getVerificationStatus();

// $sigVerificationResult is a string and will either be "SUCCESS" or "FAILURE"


//if not webhook signature failed validation exit
        if ($sigVerificationResult != "SUCCESS") {
            Log::info('Failed webhook signature verification', [
                'body' => $requestBody
            ]);
            exit(1);
        } else if ($sigVerificationResult == "SUCCESS") {

//paypay webhook signature is valid

//proceed to process webhook payload


//decode raw request body

            $requestBodyDecode = json_decode($requestBody);


//pull whatever info required from decoded request body, some examples below
            $paymentSystemID = $requestBodyDecode->id;


            $eventType = $requestBodyDecode->event_type;
            switch ($eventType) {
                case 'CHECKOUT.ORDER.APPROVED':
                    $this->captureOrder($requestBodyDecode->resource->id);
                    break;
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->markPurchaseAsCompleted($requestBodyDecode->resource->supplementary_data->related_ids->order_id);
                    break;
                case 'CHECKOUT.ORDER.VOIDED':
                    $this->cancelOrder($requestBodyDecode->resource->id);

            }

            $sigVerificationResult = $output->getVerificationStatus();


            Log::info('paypal webhook', [
                'data' => $requestBody
            ]);
        }
    }

    private function captureOrder(string $id)
    {
        $purchase = Purchase::where('paypal_id', $id)->first();
        if ($purchase) {
            $order = $this->paypalService->captureOrder($id);
//            $cartIdentifier = sprintf("cart-user-%d", $purchase->user_id);
//            if ($order['status'] === 'COMPLETED') {
//                $purchase->update([
//                    'completed_at' => Carbon::now(),
//                ]);
//                \Cart::erase($cartIdentifier);
//                \Cart::destroy();
//            } else {
//                Log::error('failed to capute payment');
////                return response()->json([
////                    'errors' => [
////                        'Order' => 'Order not completed',
////                    ]
////                ], 400);
//            }
        } else {
            Log::error('Failed to find purchase', [
                'paypal_id' => $id,
            ]);
        }
    }


    private function markPurchaseAsCompleted(string $id)
    {
        $purchase = Purchase::where('paypal_id', $id)->first();
        Log::info('mark order as completed', [
            'id' => $id,
            'purchase' => $purchase,
        ]);
        if ($purchase) {
            $purchase->update([
                'completed_at' => Carbon::now(),
                'status' => Purchase::STATUSES['COMPLETED_PAYMENT'],
            ]);
        } else {
            Log::error('Failed to find purchase', [
                'paypal_id' => $id,
            ]);
        }
    }

    private function cancelOrder(string $id)
    {
        $purchase = Purchase::where('paypal_id', $id)->first();
        Log::info('cancel order', [
            'id' => $id,
            'purchase' => $purchase,
        ]);
        if ($purchase) {
            foreach ($purchase->beats as $beat) {
                if ($beat->is_exclusive) {
                    $beat->update([
                        'status' => Beat::STATUSES['AVAILABLE']
                    ]);
                }
            }
            $purchase->beatPurchase()->delete();
            $purchase->update([
//                'completed_at' => Carbon::now(),
                'status' => Purchase::STATUSES['CANCELLED'],
            ]);

            $purchase->delete();
        } else {
            Log::error('Failed to find purchase', [
                'paypal_id' => $id,
            ]);
        }
    }
}
