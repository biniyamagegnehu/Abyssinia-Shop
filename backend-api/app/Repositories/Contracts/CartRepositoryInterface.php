<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface
{
    /**
     * Find a cart by user ID, or create one if it doesn't exist.
     */
    public function findByUser(int $userId): ?Cart;

    /**
     * Create a new cart for a user.
     */
    public function createForUser(int $userId): Cart;

    /**
     * Clear all items from a cart.
     */
    public function clear(int $cartId): bool;
}
