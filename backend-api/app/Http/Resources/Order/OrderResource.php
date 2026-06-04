<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'subtotal' => number_format((float) ($this->total_amount - $this->shipping_cost), 2, '.', ''),
            'shipping_fee' => number_format((float) $this->shipping_cost, 2, '.', ''),
            'discount' => '0.00',
            'total' => number_format((float) $this->total_amount, 2, '.', ''),
            
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
