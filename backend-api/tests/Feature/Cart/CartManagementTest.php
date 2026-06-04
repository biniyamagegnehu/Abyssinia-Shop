<?php

namespace Tests\Feature\Cart;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $activeProduct;
    private Product $outOfStockProduct;
    private Product $archivedProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        $this->activeProduct = Product::create([
            'name' => 'Active Product',
            'slug' => 'active-product',
            'category_id' => $category->id,
            'price' => 100.00,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
            'sku' => 'ACT-1',
        ]);

        $this->outOfStockProduct = Product::create([
            'name' => 'OOS Product',
            'slug' => 'oos-product',
            'category_id' => $category->id,
            'price' => 50.00,
            'quantity' => 0,
            'status' => ProductStatus::OUT_OF_STOCK,
            'sku' => 'OOS-1',
        ]);

        $this->archivedProduct = Product::create([
            'name' => 'Archived Product',
            'slug' => 'archived-product',
            'category_id' => $category->id,
            'price' => 200.00,
            'quantity' => 5,
            'status' => ProductStatus::ARCHIVED,
            'sku' => 'ARC-1',
        ]);
    }

    public function test_user_can_add_item_to_cart()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
            'price' => 100.00,
        ]);
    }

    public function test_user_cannot_add_out_of_stock_product()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->outOfStockProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('product_id');
    }

    public function test_user_cannot_add_archived_product()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->archivedProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('product_id');
    }

    public function test_user_cannot_add_more_than_available_stock()
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 15,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors('quantity');
    }

    public function test_adding_same_product_increases_quantity()
    {
        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_view_cart_and_subtotal()
    {
        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/cart');

        $response->assertStatus(200)
                 ->assertJsonPath('data.subtotal', '200.00')
                 ->assertJsonCount(1, 'data.items')
                 ->assertJsonPath('data.items.0.line_total', '200.00');
    }

    public function test_user_can_update_cart_item()
    {
        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $cartItem = $cart->items()->first();

        $response = $this->actingAs($this->user)->putJson("/api/v1/cart/items/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 5,
        ]);
    }

    public function test_user_can_remove_cart_item()
    {
        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $cartItem = $cart->items()->first();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/cart/items/{$cartItem->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_user_can_clear_cart()
    {
        $this->actingAs($this->user)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/cart");

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_user_cannot_update_another_users_cart_item()
    {
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)->postJson('/api/v1/cart/items', [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $cart = Cart::where('user_id', $otherUser->id)->first();
        $cartItem = $cart->items()->first();

        $response = $this->actingAs($this->user)->putJson("/api/v1/cart/items/{$cartItem->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(403);
    }
}
