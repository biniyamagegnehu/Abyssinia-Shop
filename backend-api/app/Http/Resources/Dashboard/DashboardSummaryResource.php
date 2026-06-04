<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_sales' => $this->resource['total_sales'],
            'total_orders' => $this->resource['total_orders'],
            'total_customers' => $this->resource['total_customers'],
            'total_products' => $this->resource['total_products'],
            'average_order_value' => $this->resource['average_order_value'],
        ];
    }
}
