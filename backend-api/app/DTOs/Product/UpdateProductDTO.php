<?php

namespace App\DTOs\Product;

use App\Enums\ProductStatus;

readonly class UpdateProductDTO
{
    public function __construct(
        public int $category_id,
        public string $name,
        public string $slug,
        public float $price,
        public ?float $compare_at_price,
        public string $sku,
        public int $quantity,
        public ProductStatus $status,
        public ?string $description = null,
    ) {}
}
