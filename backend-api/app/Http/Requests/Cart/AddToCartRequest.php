<?php

namespace App\Http\Requests\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authentication handled by auth:sanctum middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Convert the request into a DTO.
     */
    public function toDTO(int $userId): \App\DTOs\Cart\AddToCartDTO
    {
        return new \App\DTOs\Cart\AddToCartDTO(
            user_id: $userId,
            product_id: (int) $this->validated('product_id'),
            quantity: (int) $this->validated('quantity')
        );
    }
}
