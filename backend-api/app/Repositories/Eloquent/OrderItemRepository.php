<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderItem;
use App\Repositories\Contracts\OrderItemRepositoryInterface;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function create(array $data): OrderItem
    {
        return OrderItem::create($data);
    }

    /**
     * Insert multiple order items.
     *
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        return OrderItem::insert($data);
    }
}
