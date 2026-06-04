<?php

namespace Tests\Feature\Api\V1\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::firstOrCreate(['name' => 'view-dashboard', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('view-dashboard');

        $this->customer = User::factory()->create();
    }

    public function test_admin_can_view_dashboard_summary()
    {
        $address = Address::factory()->create(['user_id' => $this->customer->id]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'status' => OrderStatus::DELIVERED,
            'total_amount' => 2650.00,
            'shipping_cost' => 150.00,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_sales',
                    'total_orders',
                    'total_customers',
                    'total_products',
                    'average_order_value',
                ],
            ]);
    }

    public function test_admin_can_view_order_statistics()
    {
        $address = Address::factory()->create(['user_id' => $this->customer->id]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'status' => OrderStatus::PENDING,
        ]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
            'status' => OrderStatus::DELIVERED,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'pending',
                    'processing',
                    'shipped',
                    'delivered',
                    'cancelled',
                    'refunded',
                ],
            ]);
    }

    public function test_admin_can_view_revenue_analytics_monthly()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/revenue?period=monthly');

        $response->assertStatus(200)
            ->assertJsonPath('data.period', 'monthly')
            ->assertJsonStructure([
                'data' => [
                    'period',
                    'data',
                ],
            ]);
    }

    public function test_revenue_rejects_invalid_period()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/revenue?period=yearly');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid revenue period. Use: daily, weekly, or monthly.',
            ]);
    }

    public function test_admin_can_view_recent_orders()
    {
        $address = Address::factory()->create(['user_id' => $this->customer->id]);
        Order::factory()->create([
            'user_id' => $this->customer->id,
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/recent-orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'order_number', 'customer_name', 'status', 'total', 'created_at'],
                ],
            ]);
    }

    public function test_admin_can_view_low_stock_products()
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Low Stock Item',
            'sku' => 'LOW-001',
            'quantity' => 3,
            'status' => ProductStatus::ACTIVE,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Stocked Item',
            'sku' => 'STOCK-001',
            'quantity' => 50,
            'status' => ProductStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/low-stock');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Low Stock Item', $data[0]['name']);
        $this->assertEquals(3, $data[0]['stock']);
    }

    public function test_low_stock_respects_custom_threshold()
    {
        $category = Category::factory()->create();
        Product::factory()->create([
            'category_id' => $category->id,
            'quantity' => 8,
            'status' => ProductStatus::ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/low-stock?threshold=10');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));

        $response = $this->actingAs($this->admin)->getJson('/api/v1/admin/dashboard/low-stock?threshold=5');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_customer_cannot_access_dashboard()
    {
        $response = $this->actingAs($this->customer)->getJson('/api/v1/admin/dashboard/summary');
        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/v1/admin/dashboard/summary');
        $response->assertStatus(401);
    }
}
