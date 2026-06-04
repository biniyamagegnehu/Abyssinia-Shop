<?php

namespace App\Services\Product;

use App\DTOs\Product\CreateProductDTO;
use App\DTOs\Product\UpdateProductDTO;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $repository,
        protected CategoryRepositoryInterface $categoryRepository
    ) {}

    public function create(CreateProductDTO $dto): Product
    {
        if ($this->repository->slugExists($dto->slug)) {
            throw ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        if ($this->repository->skuExists($dto->sku)) {
            throw ValidationException::withMessages([
                'sku' => ['The SKU has already been taken.'],
            ]);
        }

        if (!$this->categoryRepository->findById($dto->category_id)) {
            throw ValidationException::withMessages([
                'category_id' => ['The selected category is invalid.'],
            ]);
        }

        return $this->repository->create([
            'category_id' => $dto->category_id,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'price' => $dto->price,
            'compare_at_price' => $dto->compare_at_price,
            'sku' => $dto->sku,
            'quantity' => $dto->quantity,
            'status' => $dto->status,
            'description' => $dto->description,
        ]);
    }

    public function update(Product $product, UpdateProductDTO $dto): Product
    {
        if ($this->repository->slugExists($dto->slug, $product->id)) {
            throw ValidationException::withMessages([
                'slug' => ['The slug has already been taken.'],
            ]);
        }

        if ($this->repository->skuExists($dto->sku, $product->id)) {
            throw ValidationException::withMessages([
                'sku' => ['The SKU has already been taken.'],
            ]);
        }

        if (!$this->categoryRepository->findById($dto->category_id)) {
            throw ValidationException::withMessages([
                'category_id' => ['The selected category is invalid.'],
            ]);
        }

        $this->repository->update($product, [
            'category_id' => $dto->category_id,
            'name' => $dto->name,
            'slug' => $dto->slug,
            'price' => $dto->price,
            'compare_at_price' => $dto->compare_at_price,
            'sku' => $dto->sku,
            'quantity' => $dto->quantity,
            'status' => $dto->status,
            'description' => $dto->description,
        ]);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $this->repository->delete($product);
    }
}
