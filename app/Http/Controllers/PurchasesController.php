<?php

namespace App\Http\Controllers;

use App\Exceptions\BeatUnavailableException;
use App\Http\Resources\BeatResource;
use App\Http\Services\PaypalService;
use App\Models\Beat;
use App\Models\Purchase;
use Gloudemans\Shoppingcart\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class PurchasesController extends Controller
{

    protected PaypalService $paypalService;

    /**
     * @param PaypalService $paypalService
     */
    public function __construct(PaypalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Return the beats purchased by the user
     *
     * @authenticated
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|BeatResource[]
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $beats = Beat::with(['creator', 'genres', 'artwork', 'preview'])
            ->join('beat_purchase', 'beats.id', '=', 'beat_purchase.beat_id')
            ->join('purchases', 'beat_purchase.purchase_id', '=', 'purchases.id')
            ->where('purchases.user_id', '=', $user->id)
            ->orderBy('purchases.created_at', 'desc')
            ->whereNotNull('purchases.completed_at')
            ->select(['beats.*'])
            ->paginate(25);

        return BeatResource::collection($beats);
    }

    /**
     * @authenticated
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * @authenticated
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request)
    {
        $user = $request->user();
        /** @var Cart $cart */
        $cart = \Cart::instance('default');
        $cartIdentifier = sprintf("cart-user-%d", $user->id);
        $cart->merge($cartIdentifier);

        if ($cart->count() < 1) {
            return response()->json([
                'errors' => [
                    'No beats in your cart',
                ]
            ], 400);
        }

        DB::transaction(function () use ($cart) {
            foreach ($cart->content() as $beat) {
                Log::info('beat', ['beat' => $beat, 'model' => $beat->model]);
                if ($beat->model->is_exclusive) {
                    if ($beat->model->status !== Beat::STATUSES['AVAILABLE']) {
                        throw new BeatUnavailableException(sprintf("Beat %s is not available, id: %d", $beat->model->name, $beat->model->id));
                    }
                    $beat->model->update([
                        'status' => Beat::STATUSES['PURCHASED']
                    ]);
                }
            }
        });

        $response = $this->paypalService->createOrder($user, $cart);

        return response()->json([
            'response' => $response->json(),
        ]);
    }

    /**
     * @response scenario=success {}
     * @authenticated
     * @param Request $request
     * @param Purchase $purchase
     * @return void
     */
    public function complete(Request $request, Purchase $purchase)
    {
        $user = $request->user();
        if ($user->id !== $purchase->user_id) {
            throw new UnauthorizedHttpException("You cannot complete this purchase");
        }

        Log::info('mark order as completed', [
            'purchase' => $purchase,
        ]);
        $purchase->where('status', Purchase::STATUSES['AWAITING_PAYMENT'])->update([
            'completed_at' => Carbon::now(),
            'status' => Purchase::STATUSES['COMPLETED_PAYMENT'],
        ]);

        /** @var Cart $cart */
        $cart = \Cart::instance('default');
        if ($user) {
            $cartIdentifier = sprintf("cart-user-%d", $user->id);
            $cart->erase($cartIdentifier);
        }
        $cart->destroy();
    }

    /**
     * @authenticated
     * @param Request $request
     * @param Purchase $purchase
     * @return void
     */
    public function cancel(Request $request, Purchase $purchase)
    {
        $user = $request->user();
        if ($user->id !== $purchase->user_id) {
            throw new UnauthorizedHttpException("You cannot cancel this purchase");
        }

        if ($purchase->status !== Purchase::STATUSES['COMPLETED_PAYMENT']) {
//            $purchase->beats()
            foreach ($purchase->beats as $beat) {
                if ($beat->is_exclusive) {
                    $beat->update([
                        'status' => Beat::STATUSES['AVAILABLE']
                    ]);
                }
            }
            $purchase->beatPurchase()->delete();
            $purchase->update([
                'status' => Purchase::STATUSES['CANCELLED'],
            ]);
            $purchase->delete();
        }
    }

    public function edit(Purchase $purchase)
    {
        //
    }

    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    public function destroy(Purchase $purchase)
    {
        //
    }
}
