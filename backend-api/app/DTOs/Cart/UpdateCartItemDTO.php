<?php

namespace App\DTOs\Cart;

readonly class UpdateCartItemDTO
{
    public function __construct(
        public int $user_id,
        public int $quantity
    ) {}
}
