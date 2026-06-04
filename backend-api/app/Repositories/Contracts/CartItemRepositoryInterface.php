<?php

namespace App\Repositories\Contracts;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

interface CartItemRepositoryInterface
{
    /**
     * Find a cart item by its ID.
     */
    public function findById(int $cartItemId): ?CartItem;

    /**
     * Find a specific product in a specific cart.
     */
    public function findByProduct(int $cartId, int $productId): ?CartItem;

    /**
     * Create a new cart item.
     */
    public function create(array $data): CartItem;

    /**
     * Update an existing cart item.
     */
    public function update(int $cartItemId, array $data): bool;

    /**
     * Delete a cart item.
     */
    public function delete(int $cartItemId): bool;

    /**
     * Get all items in a cart, with their related products.
     */
    public function getCartItems(int $cartId): Collection;
}
