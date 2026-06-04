<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Actions\Product\CreateProductAction;
use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductCollection;
use App\Http\Resources\Product\ProductResource;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Traits\HasApiResponses;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use HasApiResponses;

    public function __construct(
        protected ProductRepositoryInterface $repository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'status', 'sort']);
        $products = $this->repository->paginateWithFilters($filters);

        return new ProductCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request, CreateProductAction $action)
    {
        $product = $action->execute($request->toDTO());

        return $this->successResponse(
            new ProductResource($product),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product = $this->repository->findById($product->id);

        return $this->successResponse(
            new ProductResource($product),
            'Product retrieved successfully'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action)
    {
        $updatedProduct = $action->execute($product, $request->toDTO());

        return $this->successResponse(
            new ProductResource($updatedProduct),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product, DeleteProductAction $action)
    {
        $action->execute($product);

        return $this->successResponse(
            null,
            'Product deleted successfully'
        );
    }
}
