<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartStoreRequest;
use App\Http\Resources\CartItemResource;
use App\Models\Beat;
use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\Exceptions\CartAlreadyStoredException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function get(Request $request)
    {
        /** @var Cart $cart */
        $cart = \Cart::instance('default');
        $user = $request->user();

        if ($user) {
            $cartIdentifier = sprintf("cart-user-%d", $user->id);
            $cart->merge($cartIdentifier);
            try {
                $cart->store($cartIdentifier);
            } catch (CartAlreadyStoredException $e) {
                Log::error('error storing cart', [
                    'message' => $e->getMessage(),
                    'cart' => $cart,
                ]);
                $cart->merge($cartIdentifier);
                $cart->erase($cartIdentifier);
                $cart->store($cartIdentifier);
            }
        }
        $cartContentIds = [];

        foreach ($cart->content() as $key => $item) {
            $cartContentIds[] = $item->id;
        }

        $cartItems = Beat::with(['creator', 'artwork', 'preview'])
            ->whereIn('id', $cartContentIds)
            ->where('status', Beat::STATUSES['AVAILABLE'])->get();

        return CartItemResource::collection($cartItems);
    }

    /**
     * @param CartStoreRequest $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function store(CartStoreRequest $request)
    {
        $data = $request->validationData();

        $user = $request->user();
        /** @var Cart $cart */
        $cart = \Cart::instance('default');

        $beats = Beat::whereIn('id', $data['beats'])
            ->where('status', Beat::STATUSES['AVAILABLE'])
            ->get();

        Log::info('beats before add', [
            'content' => $cart->content(),
        ]);

        Log::info('Beats to add', [
            'beats' => $beats
        ]);

        if ($user) {
            $cartIdentifier = sprintf("cart-user-%d", $user->id);
            $cart->merge($cartIdentifier);
        }

        foreach ($beats as $key => $beat) {
            $found = $cart->search(function ($cartItem, $rowId) use ($beat) {
                return $cartItem->id === $beat->id;
            });

            Log::info('Beat found in cart', [
                'beat' => $beat,
            ]);

            if (count($found) === 0) {
                /** @var Beat $beat */
                $cart->add($beat->id, $beat->name, 1, $beat->price / 100, 0, ['exclusive' => $beat->is_exclusive])->associate(Beat::class);
                Log::info('Beat added in cart', [
                    'beat' => $beat,
                ]);
            }
        }

        Log::info('beats in cart', [
            'content' => $cart->content(),
        ]);


        if ($user) {
            $cartIdentifier = sprintf("cart-user-%d", $user->id);
            $cart->merge($cartIdentifier);
            try {
                $cart->store($cartIdentifier);
            } catch (CartAlreadyStoredException $e) {
                Log::error('error storing cart', [
                    'message' => $e->getMessage(),
                    'cart' => $cart,
                ]);
                $cart->erase($cartIdentifier);
                $cart->store($cartIdentifier);
            }
        }

        $cartContentIds = [];
        foreach ($cart->content() as $key => $item) {
            $cartContentIds[] = $item->id;
        }

        $cartItems = Beat::with(['creator', 'artwork', 'preview'])
        ->whereIn('id', $cartContentIds)
        ->where('status', Beat::STATUSES['AVAILABLE'])->get();

        return CartItemResource::collection($cartItems);
    }

    /**
     * Remove beats from the cart
     *
     * @param Request $request
     * @param Beat $beat
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function remove(Request $request, Beat $beat)
    {
        $data = $request->validate([
            'beats' => ['required', 'array'],
            'beats.*' => ['required', 'integer', 'exists:beats,id']
        ]);

        $user = $request->user();
        $cart = \Cart::instance('default');

        $cartIdentifier = "";
        if ($user) {
            $cartIdentifier = sprintf("cart-user-%d", $user->id);
            $cart->merge($cartIdentifier);
            foreach ($cart->content() as $key => $beat) {
                $cart->update($key, 1);
            }
            $cart->erase($cartIdentifier);
            $cart->store($cartIdentifier);
        }

        foreach ($data['beats'] as $beatId) {
            $found = $cart->search(function ($cartItem, $rowId) use ($beatId) {
                return $cartItem->id == $beatId;
            });

            if ($found->isNotEmpty()) {
                $cart->remove($found->first()->rowId);
            }
        }

        if ($user) {
            $cartIdentifier = sprintf("cart-user-%d", $user->id);
            $cart->erase($cartIdentifier);
            $cart->store($cartIdentifier);
        }

        $cartContentIds = [];
        foreach ($cart->content() as $key => $item) {
            $cartContentIds[] = $item->id;
        }

        $cartItems = Beat::with(['creator', 'artwork', 'preview'])
            ->whereIn('id', $cartContentIds)
            ->where('status', Beat::STATUSES['AVAILABLE'])->get();

        return CartItemResource::collection($cartItems);
    }

    /**
     * @param Request $request
     * @return void
     */
    public function destroy(Request $request)
    {
        /** @var Cart $cart */
        $cart = \Cart::instance('default');
        $user = $request->user();
        if ($user) {
            $cartIdentifier = sprintf("cart-user-%d", $user->id);
            $cart->erase($cartIdentifier);
        }
        $cart->destroy();
    }
}
