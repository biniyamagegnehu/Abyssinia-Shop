<?php

namespace App\Actions\Order;

use App\DTOs\Order\UpdateOrderStatusDTO;
use App\Models\Order;
use App\Services\Order\OrderService;

class UpdateOrderStatusAction
{
    public function __construct(
        private readonly OrderService $orderService
    ) {
    }

    public function execute(Order $order, UpdateOrderStatusDTO $dto): Order
    {
        return $this->orderService->updateOrderStatus($order, $dto->status);
    }
}
