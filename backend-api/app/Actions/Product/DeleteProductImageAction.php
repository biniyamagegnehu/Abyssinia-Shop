<?php

namespace App\Actions\Product;

use App\Models\ProductImage;
use App\Services\Product\ProductImageService;

class DeleteProductImageAction
{
    public function __construct(
        protected ProductImageService $service
    ) {}

    public function execute(ProductImage $image): void
    {
        $this->service->delete($image);
    }
}
