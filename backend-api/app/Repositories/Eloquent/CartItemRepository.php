<?php

namespace App\Repositories\Eloquent;

use App\Models\CartItem;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CartItemRepository implements CartItemRepositoryInterface
{
    /**
     * Find a cart item by its ID.
     */
    public function findById(int $cartItemId): ?CartItem
    {
        return CartItem::find($cartItemId);
    }

    /**
     * Find a specific product in a specific cart.
     */
    public function findByProduct(int $cartId, int $productId): ?CartItem
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    /**
     * Create a new cart item.
     */
    public function create(array $data): CartItem
    {
        return CartItem::create($data);
    }

    /**
     * Update an existing cart item.
     */
    public function update(int $cartItemId, array $data): bool
    {
        $cartItem = $this->findById($cartItemId);
        if ($cartItem) {
            return $cartItem->update($data);
        }
        return false;
    }

    /**
     * Delete a cart item.
     */
    public function delete(int $cartItemId): bool
    {
        $cartItem = $this->findById($cartItemId);
        if ($cartItem) {
            return $cartItem->delete();
        }
        return false;
    }

    /**
     * Get all items in a cart, with their related products.
     */
    public function getCartItems(int $cartId): Collection
    {
        return CartItem::with(['product', 'product.images' => function ($query) {
            $query->where('is_primary', true);
        }])
            ->where('cart_id', $cartId)
            ->get();
    }
}
