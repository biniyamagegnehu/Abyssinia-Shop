<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentOrderResource extends JsonResource
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
            'customer_name' => $this->user?->name ?? 'N/A',
            'status' => $this->status->value,
            'total' => number_format((float) $this->total_amount, 2, '.', ''),
            'created_at' => $this->created_at,
        ];
    }
}
