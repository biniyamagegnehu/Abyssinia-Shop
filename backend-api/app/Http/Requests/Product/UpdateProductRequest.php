<?php

namespace App\Http\Requests\Product;

use App\DTOs\Product\UpdateProductDTO;
use App\Enums\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Enforced via Sanctum and Spatie middleware on route
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['required', 'string', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
        ];
    }

    /**
     * Convert validation request to DTO.
     */
    public function toDTO(): UpdateProductDTO
    {
        return new UpdateProductDTO(
            category_id: (int) $this->validated('category_id'),
            name: $this->validated('name'),
            slug: $this->validated('slug'),
            price: (float) $this->validated('price'),
            compare_at_price: $this->validated('compare_at_price') !== null ? (float) $this->validated('compare_at_price') : null,
            sku: $this->validated('sku'),
            quantity: (int) $this->validated('stock'),
            status: ProductStatus::from($this->validated('status')),
            description: $this->validated('description'),
        );
    }
}
