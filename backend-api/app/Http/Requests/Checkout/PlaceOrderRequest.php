<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping_address_id' => [
                'required',
                'integer',
                'exists:addresses,id,user_id,' . $this->user()->id
            ],
            'billing_address_id' => [
                'required',
                'integer',
                'exists:addresses,id,user_id,' . $this->user()->id
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address_id.exists' => 'Shipping address does not belong to authenticated user.',
            'billing_address_id.exists' => 'Billing address does not belong to authenticated user.',
        ];
    }
}
