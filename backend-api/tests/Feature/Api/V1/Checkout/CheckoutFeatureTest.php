<?php

namespace Tests\Feature\Api\V1\Checkout;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Address $shippingAddress;
    private Address $billingAddress;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $this->shippingAddress = Address::factory()->create([
            'user_id' => $this->user->id,
        ]);
        
        $this->billingAddress = Address::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_checkout_summary_returns_correct_totals()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 1250.00,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE
        ]);
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 1250.00
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/checkout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'subtotal' => '2500.00',
                    'shipping_fee' => '150.00',
                    'discount' => '0.00',
                    'total' => '2650.00',
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'quantity' => 2,
                            'price' => '1250.00',
                            'line_total' => '2500.00'
                        ]
                    ]
                ]
            ]);
    }

    public function test_cannot_checkout_empty_cart()
    {
        Cart::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/checkout/place-order', [
            'shipping_address_id' => $this->shippingAddress->id,
            'billing_address_id' => $this->billingAddress->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Your cart is empty'
            ]);
    }

    public function test_address_validation_fails_for_unowned_address()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => ProductStatus::ACTIVE
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $otherUserAddress = Address::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/checkout/place-order', [
            'shipping_address_id' => $otherUserAddress->id,
            'billing_address_id' => $this->billingAddress->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['shipping_address_id']);
    }

    public function test_fails_when_insufficient_stock()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'quantity' => 1, // Only 1 in stock
            'status' => ProductStatus::ACTIVE
        ]);
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2 // Trying to buy 2
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/checkout/place-order', [
            'shipping_address_id' => $this->shippingAddress->id,
            'billing_address_id' => $this->billingAddress->id,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient stock for ' . $product->name
            ]);
    }

    public function test_successful_checkout()
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 1000.00,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE
        ]);
        
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 1000.00
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/checkout/place-order', [
            'shipping_address_id' => $this->shippingAddress->id,
            'billing_address_id' => $this->billingAddress->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.subtotal', '2000.00')
            ->assertJsonPath('data.shipping_fee', '150.00')
            ->assertJsonPath('data.total', '2150.00');

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => OrderStatus::PENDING->value,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Inventory reduced
        $this->assertEquals(8, $product->fresh()->quantity);

        // Cart cleared
        $this->assertEquals(0, $cart->items()->count());
    }
}
