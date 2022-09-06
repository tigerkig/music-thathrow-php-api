<?php

namespace App\Http\Services;

use App\Models\Purchase;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Gloudemans\Shoppingcart\Cart;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PaypalService
{
    public function getSignUpLink(string $userId)
    {
        $token = $this->getAccessToken();
        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])->post(env('PAYPAL_API_URL') . '/v2/customer/partner-referrals', [
                'tracking_id' => $userId,
                'operations' => [
                    [
                        "operation" => "API_INTEGRATION",
                        "api_integration_preference" => [
                            "rest_api_integration" => [
                                "integration_method" => "PAYPAL",
                                "integration_type" => "THIRD_PARTY",
                                "third_party_details" => [
                                    "features" => [
                                        "PAYMENT",
                                        "REFUND",
                                        "DELAY_FUNDS_DISBURSEMENT",
                                        "PARTNER_FEE"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "products" => [
                    'EXPRESS_CHECKOUT'
                ],
                "legal_contents" => [
                    [
                        "type" => "SHARE_DATA_CONSENT",
                        "granted" => true
                    ]
                ],
                'partner_config_override' => [
                    'return_url' => RouteServiceProvider::HOME . 'onboarding_complete',
                    'action_renewal_url' => RouteServiceProvider::HOME . 'onboarding',
                ]
            ]);

        return $response['links'][1]['href'];
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        $response = Http::withBasicAuth(config('paypal.client_id'), config('paypal.client_secret'))
            ->withHeaders([
                'Accept' => 'application/json',
                'Accept-Language' => 'en_GB',
            ])->asForm()
            ->post(config('paypal.url') . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        return $response['access_token'];
    }

    public function getOnboardingStatus(string $merchantId)
    {
        $token = $this->getAccessToken();
        return Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])->get(env('PAYPAL_API_URL') . '/v1/customer/partners/' . env('PAYPAL_MERCHANT_ID') . '/merchant-integrations/' . $merchantId);
    }

    public function createOrder(User $user, Cart $cart): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\JsonResponse|\Illuminate\Http\Client\Response
    {
        $purchase = new Purchase();
        $purchase->user_id = $user->id;
        $purchase->total = 0;
        $purchase->save();
        $purchaseUnits = [];


        $alreadyBoughtList = [];
        $total = 0;
        foreach ($cart->content() as $beat) {
            $alreadyBought = DB::table('beat_purchase')
                ->where('beat_id', $beat->id)
                ->join('purchases', 'purchases.id', '=', 'beat_purchase.purchase_id')
                ->where('purchases.user_id', $user->id)
                ->count();
            if ($alreadyBought) {
                $alreadyBoughtList[] = $beat;
                continue;
            }

            $purchaseUnit = [
                'reference_id' => 'beat_purchase_' . $purchase->id . '_' . $beat->id ,
                'amount' => [
                    'currency_code' => 'GBP',
                    'value' => $beat->price,
                ],
                'description' => Str::substr($beat->name, 0, 127),
                "payee" => [
                        "email_address" => config('paypal.email')
              ],
                'payment_instruction' => [
                    'disbursement_mode' => 'INSTANT',
                    'invoice_id' => $purchase->id,
                ]
            ];

            $purchaseUnits[] = $purchaseUnit;

            $purchase->beats()->attach($beat->id, [
                'price' => $beat->price * 100
            ]);
            $total += $beat->price * 100;
        }

        if (count($purchaseUnits) === 0) {
            $purchase->beats()->detach();
            $purchase->delete();
            throw new BadRequestHttpException('No available beat to buy. Maybe you already bought them, they were remove or if exclusives, they were bought by someone else');
//            return response()->json(
//                [
//                    'error' => 'No available beat to buy. Maybe you already bought them, they were remove or if exclusives, they were bought by someone else'
//                ],
//                400
//            );
//            return redirect()->route('cart.index')->with('error', 'You have already bought all the beats in your cart');
        }

        $purchase->update([
            'total' => $total,
        ]);

        Log::info('Before token');

        $token = $this->getAccessToken();

        Log::info('After token');
        $response = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])->post(config('paypal.url') . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => $purchaseUnits,
                'application_context' => [
                    'return_url' => env('FRONTEND_URL') .'/purchases/' . $purchase->id . '/complete',
                    'cancel_url' => env('FRONTEND_URL') .'/purchases/' . $purchase->id . '/cancel',
                ]
            ]);

        Log::info('After response');
        Log::info('createOrderResponse', [
            'response' => $response,
        ]);

        $purchase->update([
            'paypal_id' => $response['id'],
//            'provider' => 'PAYPAL',
        ]);

        return $response;
    }

    public function captureOrder(string $orderId)
    {
        $token = $this->getAccessToken();
        $client = new Client();
        $response = $client
            ->request('POST', config('paypal.url') . '/v2/checkout/orders/' . $orderId . '/capture', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ]
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function sendMoneyToSellers($order)
    {
        $purchaseUnits = $order['purchase_units'];
        $token = $this->getAccessToken();
        foreach ($purchaseUnits as $purchaseUnit) {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])->post(env('PAYPAL_API_URL') . '/v1/payments/referenced-payouts-items', [
                    'reference_id' => $purchaseUnit['payments']['captures'][0]['id'],
                    'reference_type' => 'TRANSACTION_ID',
                ]);
        }

        $purchase = Purchase::where('provider_id', '=', $order['id'])->first();
        if ($purchase) {
            $purchase->update([
                'confirmed_at' => now(),
            ]);
        }
    }

    /**
     * Helper method for getting an APIContext for all calls
     */
    public function getApiContext()
    {

        // #### SDK configuration
        // Register the sdk_config.ini file in current directory
        // as the configuration source.
        /*
        if(!defined("PP_CONFIG_PATH")) {
            define("PP_CONFIG_PATH", __DIR__);
        }
        */


        // ### Api context
        // Use an ApiContext object to authenticate
        // API calls. The clientId and clientSecret for the
        // OAuthTokenCredential class can be retrieved from
        // developer.paypal.com

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                config('paypal.client_id'),
                config('paypal.client_secret')
            )
        );

        // Comment this line out and uncomment the PP_CONFIG_PATH
        // 'define' block if you want to use static file
        // based configuration

        $apiContext->setConfig(
            array(
                'mode' => 'sandbox',
                'log.LogEnabled' => false,
                'log.FileName' => '../PayPal.log',
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => false,
                //'cache.FileName' => '/PaypalCache' // for determining paypal cache directory
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );

        // Partner Attribution Id
        // Use this header if you are a PayPal partner. Specify a unique BN Code to receive revenue attribution.
        // To learn more or to request a BN Code, contact your Partner Manager or visit the PayPal Partner Portal
        // $apiContext->addRequestHeader('PayPal-Partner-Attribution-Id', '123123123');

        return $apiContext;
    }


    public function markPurchaseAsCompleted(string $id)
    {

    }
}
