<?php

namespace Database\Factories\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'product_name' => fake()->words(3, true),
            'price' => fake()->randomFloat(2, 50, 2000),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
