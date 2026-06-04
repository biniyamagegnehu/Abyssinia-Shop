<?php

namespace App\Http\Resources\Checkout;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'subtotal' => number_format((float) $this->resource['subtotal'], 2, '.', ''),
            'shipping_fee' => number_format((float) $this->resource['shipping_fee'], 2, '.', ''),
            'discount' => number_format((float) $this->resource['discount'], 2, '.', ''),
            'total' => number_format((float) $this->resource['total'], 2, '.', ''),
            'items' => array_map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => number_format((float) $item['price'], 2, '.', ''),
                    'line_total' => number_format((float) $item['line_total'], 2, '.', ''),
                ];
            }, $this->resource['items']),
        ];
    }
}
