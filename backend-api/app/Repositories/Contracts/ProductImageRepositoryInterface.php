<?php

namespace App\Repositories\Contracts;

use App\Models\ProductImage;

interface ProductImageRepositoryInterface
{
    public function findById(int $id): ?ProductImage;

    public function create(array $data): ProductImage;

    public function delete(ProductImage $image): bool;

    public function unsetPrimaryForProduct(int $productId): void;

    public function setPrimary(ProductImage $image): bool;
}
