<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Services\Product\ProductService;

class DeleteProductAction
{
    public function __construct(protected ProductService $service)
    {}

    public function execute(Product $product): void
    {
        $this->service->delete($product);
    }
}
