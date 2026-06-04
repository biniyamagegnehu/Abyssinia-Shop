<?php

namespace App\Actions\Product;

use App\DTOs\Product\CreateProductDTO;
use App\Models\Product;
use App\Services\Product\ProductService;

class CreateProductAction
{
    public function __construct(protected ProductService $service)
    {}

    public function execute(CreateProductDTO $dto): Product
    {
        return $this->service->create($dto);
    }
}
