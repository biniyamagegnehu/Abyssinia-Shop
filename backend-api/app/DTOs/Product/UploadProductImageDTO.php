<?php

namespace App\DTOs\Product;

use Illuminate\Http\UploadedFile;

readonly class UploadProductImageDTO
{
    public function __construct(
        public int $product_id,
        public UploadedFile $image,
        public bool $is_primary = false,
        public int $sort_order = 0,
    ) {}
}
