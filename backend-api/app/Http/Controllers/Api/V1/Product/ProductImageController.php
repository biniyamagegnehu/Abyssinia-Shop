<?php

namespace App\Http\Controllers\Api\V1\Product;

use App\Http\Controllers\Controller;
use App\Actions\Product\DeleteProductImageAction;
use App\Actions\Product\SetPrimaryProductImageAction;
use App\Actions\Product\UploadProductImageAction;
use App\Http\Requests\Product\UploadProductImageRequest;
use App\Http\Resources\Product\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Product $product): AnonymousResourceCollection
    {
        return ProductImageResource::collection($product->images()->orderBy('sort_order')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        UploadProductImageRequest $request,
        Product $product,
        UploadProductImageAction $action
    ): JsonResponse {
        $image = $action->execute($request->toDTO($product->id));

        return response()->json([
            'message' => 'Product image uploaded successfully.',
            'data' => new ProductImageResource($image),
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Product $product,
        ProductImage $image,
        DeleteProductImageAction $action
    ): JsonResponse {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Image does not belong to this product.'], 403);
        }

        $action->execute($image);

        return response()->json([
            'message' => 'Product image deleted successfully.',
        ]);
    }

    /**
     * Set the image as primary for the product.
     */
    public function setPrimary(
        Product $product,
        ProductImage $image,
        SetPrimaryProductImageAction $action
    ): JsonResponse {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Image does not belong to this product.'], 403);
        }

        $action->execute($image);

        return response()->json([
            'message' => 'Product image set as primary successfully.',
            'data' => new ProductImageResource($image->refresh()),
        ]);
    }
}
