<?php

namespace Tests\Feature\Api\V1\Order;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminOrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Order $order1;
    private Order $order2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create manage-orders permission if it doesn't exist
        Permission::firstOrCreate(['name' => 'manage-orders', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('manage-orders');

        $this->customer = User::factory()->create();

        $address = Address::factory()->create(['user_id' => $this->customer->id]);

        $this->order1 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'status' => OrderStatus::PENDING,
            'order_number' => 'ABS-20260604-00001'
        ]);

        $this->order2 = Order::factory()->create([
            'user_id' => $this->customer->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'status' => OrderStatus::DELIVERED,
            'order_number' => 'ABS-20260604-00002'
        ]);
    }

    public function test_admin_can_list_all_orders()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_filter_orders_by_status()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/orders?status=' . OrderStatus::DELIVERED->value);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->order2->id);
    }

    public function test_admin_can_search_orders_by_number()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/orders?search=00001');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->order1->id);
    }

    public function test_customer_cannot_access_admin_endpoints()
    {
        $response = $this->actingAs($this->customer)->getJson('/api/v1/admin/orders');
        $response->assertStatus(403);
    }

    public function test_admin_can_update_order_status_valid_transition()
    {
        $response = $this->actingAs($this->admin)->patchJson('/api/v1/admin/orders/' . $this->order1->id . '/status', [
            'status' => OrderStatus::PROCESSING->value
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::PROCESSING->value);
            
        $this->assertEquals(OrderStatus::PROCESSING, $this->order1->fresh()->status);
    }

    public function test_admin_cannot_update_order_status_invalid_transition()
    {
        // order2 is DELIVERED. Transition to PENDING is invalid.
        $response = $this->actingAs($this->admin)->patchJson('/api/v1/admin/orders/' . $this->order2->id . '/status', [
            'status' => OrderStatus::PENDING->value
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid order status transition from ' . OrderStatus::DELIVERED->value . ' to ' . OrderStatus::PENDING->value
            ]);
            
        $this->assertEquals(OrderStatus::DELIVERED, $this->order2->fresh()->status);
    }
}
