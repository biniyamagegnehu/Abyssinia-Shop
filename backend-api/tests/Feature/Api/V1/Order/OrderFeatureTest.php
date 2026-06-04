<?php

namespace Tests\Feature\Api\V1\Order;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private Order $userOrder;
    private Order $otherUserOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $address = Address::factory()->create(['user_id' => $this->user->id]);
        $otherAddress = Address::factory()->create(['user_id' => $this->otherUser->id]);

        $this->userOrder = Order::factory()->create([
            'user_id' => $this->user->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
        ]);
        OrderItem::factory()->create(['order_id' => $this->userOrder->id]);

        $this->otherUserOrder = Order::factory()->create([
            'user_id' => $this->otherUser->id,
            'shipping_address_id' => $otherAddress->id,
            'billing_address_id' => $otherAddress->id,
        ]);
        OrderItem::factory()->create(['order_id' => $this->otherUserOrder->id]);
    }

    public function test_customer_can_list_own_orders()
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $this->userOrder->id)
            ->assertJsonMissing(['id' => $this->otherUserOrder->id]);
    }

    public function test_customer_can_view_own_order_details()
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/orders/' . $this->userOrder->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->userOrder->id)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'subtotal',
                    'shipping_fee',
                    'discount',
                    'total',
                    'items',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_customer_cannot_view_others_order()
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/orders/' . $this->otherUserOrder->id);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You do not have access to this order'
            ]);
    }
}
