<?php

namespace Database\Factories\Product;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 50, 5000),
            'compare_at_price' => null,
            'sku' => strtoupper(fake()->unique()->bothify('???-#####')),
            'quantity' => fake()->numberBetween(0, 100),
            'status' => ProductStatus::ACTIVE->value,
        ];
    }
}
