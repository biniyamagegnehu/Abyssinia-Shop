<?php

namespace App\Actions\Product;

use App\DTOs\Product\UpdateProductDTO;
use App\Models\Product;
use App\Services\Product\ProductService;

class UpdateProductAction
{
    public function __construct(protected ProductService $service)
    {}

    public function execute(Product $product, UpdateProductDTO $dto): Product
    {
        return $this->service->update($product, $dto);
    }
}
