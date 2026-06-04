<?php

namespace App\Repositories\Contracts;

use App\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * Create a new order.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order;

    /**
     * Paginate orders for a specific user.
     */
    public function paginateForUser(int $userId, int $perPage = 15);

    /**
     * Find a specific order for a user.
     */
    public function findForUser(int $orderId, int $userId): ?Order;

    /**
     * Paginate all orders (Admin).
     */
    public function paginateAll(int $perPage = 15);

    /**
     * Find an order by ID.
     */
    public function findById(int $orderId): ?Order;

    /**
     * Search orders by order number.
     */
    public function search(string $keyword, int $perPage = 15);

    /**
     * Filter orders by status.
     */
    public function filterByStatus(string $status, int $perPage = 15);

    /**
     * Search and Filter combined.
     */
    public function searchAndFilter(?string $search, ?string $status, int $perPage = 15);

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): bool;
}
