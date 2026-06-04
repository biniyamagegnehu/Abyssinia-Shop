<?php

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadProductImageRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_primary' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Convert validation request to DTO.
     */
    public function toDTO(int $productId): \App\DTOs\Product\UploadProductImageDTO
    {
        return new \App\DTOs\Product\UploadProductImageDTO(
            product_id: $productId,
            image: $this->file('image'),
            is_primary: (bool) $this->input('is_primary', false),
            sort_order: (int) $this->input('sort_order', 0),
        );
    }
}
