<?php

namespace App\Services\Order;

use App\DTOs\Order\UpdateOrderStatusDTO;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Exception;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    public function getCustomerOrders(int $userId, int $perPage = 15)
    {
        return $this->orderRepository->paginateForUser($userId, $perPage);
    }

    public function getCustomerOrder(int $orderId, int $userId): ?Order
    {
        return $this->orderRepository->findForUser($orderId, $userId);
    }

    public function getAdminOrders(?string $search, ?string $status, int $perPage = 15)
    {
        return $this->orderRepository->searchAndFilter($search, $status, $perPage);
    }

    public function getAdminOrder(int $orderId): ?Order
    {
        return $this->orderRepository->findById($orderId);
    }

    /**
     * Allowed transitions:
     * PENDING -> PROCESSING
     * PENDING -> CANCELLED
     * PROCESSING -> SHIPPED
     * SHIPPED -> DELIVERED
     * DELIVERED -> REFUNDED
     */
    public function validateStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $transitions = [
            OrderStatus::PENDING->value => [
                OrderStatus::PROCESSING->value,
                OrderStatus::CANCELLED->value,
            ],
            OrderStatus::PROCESSING->value => [
                OrderStatus::SHIPPED->value,
            ],
            OrderStatus::SHIPPED->value => [
                OrderStatus::DELIVERED->value,
            ],
            OrderStatus::DELIVERED->value => [
                OrderStatus::REFUNDED->value,
            ],
        ];

        if (!isset($transitions[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, $transitions[$currentStatus]);
    }

    public function updateOrderStatus(Order $order, string $newStatus): Order
    {
        if (!$this->validateStatusTransition($order->status->value, $newStatus)) {
            throw new Exception("Invalid order status transition from {$order->status->value} to {$newStatus}");
        }

        $this->orderRepository->updateStatus($order, $newStatus);

        return $order->fresh(['items.product', 'shippingAddress', 'billingAddress']);
    }
}
