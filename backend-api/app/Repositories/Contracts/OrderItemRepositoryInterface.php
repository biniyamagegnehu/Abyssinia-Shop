<?php

namespace App\Repositories\Contracts;

use App\Models\OrderItem;

interface OrderItemRepositoryInterface
{
    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function create(array $data): OrderItem;
    
    /**
     * Insert multiple order items.
     *
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool;
}
