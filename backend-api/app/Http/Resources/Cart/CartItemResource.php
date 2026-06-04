<?php

namespace App\Http\Resources\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $product = $this->whenLoaded('product');
        $primaryImage = $product ? $product->images->first() : null;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $product ? $product->name : null,
            'product_slug' => $product ? $product->slug : null,
            'product_image' => $primaryImage ? $primaryImage->image_path : null,
            'price' => number_format($this->price, 2, '.', ''),
            'quantity' => $this->quantity,
            'line_total' => number_format($this->price * $this->quantity, 2, '.', ''),
        ];
    }
}
