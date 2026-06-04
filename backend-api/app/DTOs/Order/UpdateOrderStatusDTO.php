<?php

namespace App\DTOs\Order;

class UpdateOrderStatusDTO
{
    public function __construct(
        public readonly string $status
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            status: $data['status']
        );
    }
}
