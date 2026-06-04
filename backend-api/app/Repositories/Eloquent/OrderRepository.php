<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Create a new order.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function paginateForUser(int $userId, int $perPage = 15)
    {
        return Order::where('user_id', $userId)
            ->with(['items.product', 'shippingAddress', 'billingAddress'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findForUser(int $orderId, int $userId): ?Order
    {
        return Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->with(['items.product', 'shippingAddress', 'billingAddress'])
            ->first();
    }

    public function paginateAll(int $perPage = 15)
    {
        return Order::with(['user', 'items.product', 'shippingAddress', 'billingAddress'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $orderId): ?Order
    {
        return Order::with(['user', 'items.product', 'shippingAddress', 'billingAddress'])
            ->find($orderId);
    }

    public function search(string $keyword, int $perPage = 15)
    {
        return Order::with(['user', 'items.product', 'shippingAddress', 'billingAddress'])
            ->where('order_number', 'like', "%{$keyword}%")
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function filterByStatus(string $status, int $perPage = 15)
    {
        return Order::with(['user', 'items.product', 'shippingAddress', 'billingAddress'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function searchAndFilter(?string $search, ?string $status, int $perPage = 15)
    {
        $query = Order::with(['user', 'items.product', 'shippingAddress', 'billingAddress']);

        if ($search) {
            $query->where('order_number', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }
}
