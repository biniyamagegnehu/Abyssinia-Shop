<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    /**
     * Find a cart by user ID.
     */
    public function findByUser(int $userId): ?Cart
    {
        return Cart::where('user_id', $userId)->first();
    }

    /**
     * Create a new cart for a user.
     */
    public function createForUser(int $userId): Cart
    {
        return Cart::create(['user_id' => $userId]);
    }

    /**
     * Clear all items from a cart.
     */
    public function clear(int $cartId): bool
    {
        $cart = Cart::find($cartId);
        if ($cart) {
            $cart->items()->delete();
            return true;
        }
        return false;
    }
}
