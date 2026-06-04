<?php

namespace Database\Factories\Order;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100, 5000);
        $shippingCost = 150.00;

        return [
            'user_id' => User::factory(),
            'shipping_address_id' => null,
            'billing_address_id' => null,
            'order_number' => 'ABS-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'status' => fake()->randomElement(OrderStatus::cases())->value,
            'total_amount' => $subtotal + $shippingCost,
            'shipping_cost' => $shippingCost,
            'tax_amount' => 0.00,
            'notes' => null,
        ];
    }
}
