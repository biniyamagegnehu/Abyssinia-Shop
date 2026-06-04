<?php

namespace App\Services\Product;

use App\DTOs\Product\UploadProductImageDTO;
use App\Models\ProductImage;
use App\Repositories\Contracts\ProductImageRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class ProductImageService
{
    public function __construct(
        protected ProductImageRepositoryInterface $repository
    ) {}

    public function upload(UploadProductImageDTO $dto): ProductImage
    {
        $path = $dto->image->store('product-images', 'public');

        if ($dto->is_primary) {
            $this->repository->unsetPrimaryForProduct($dto->product_id);
        }

        return $this->repository->create([
            'product_id' => $dto->product_id,
            'image_path' => $path,
            'is_primary' => $dto->is_primary,
            'sort_order' => $dto->sort_order,
        ]);
    }

    public function delete(ProductImage $image): void
    {
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $this->repository->delete($image);
    }

    public function setPrimary(ProductImage $image): void
    {
        $this->repository->unsetPrimaryForProduct($image->product_id);
        $this->repository->setPrimary($image);
    }
}
