<?php

namespace App\DTOs\Cart;

readonly class AddToCartDTO
{
    public function __construct(
        public int $user_id,
        public int $product_id,
        public int $quantity
    ) {}
}
