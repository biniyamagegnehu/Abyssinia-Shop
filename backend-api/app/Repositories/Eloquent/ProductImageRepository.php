<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;

class ProductImageRepository implements ProductImageRepositoryInterface
{
    public function findById(int $id): ?ProductImage
    {
        return ProductImage::find($id);
    }

    public function create(array $data): ProductImage
    {
        return ProductImage::create($data);
    }

    public function delete(ProductImage $image): bool
    {
        return $image->delete();
    }

    public function unsetPrimaryForProduct(int $productId): void
    {
        ProductImage::where('product_id', $productId)
            ->update(['is_primary' => false]);
    }

    public function setPrimary(ProductImage $image): bool
    {
        return $image->update(['is_primary' => true]);
    }
}
