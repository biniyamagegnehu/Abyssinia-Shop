<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Enums\ProductStatus;
use App\Enums\CategoryStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions and roles
        $manageProducts = Permission::firstOrCreate(['name' => 'manage-products', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($manageProducts);

        // Create a default category
        $this->category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => CategoryStatus::ACTIVE,
        ]);
    }

    protected function getAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        return $user;
    }

    protected function getCustomerUser(): User
    {
        return User::factory()->create();
    }

    public function test_can_list_products()
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_products_by_search()
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
            'description' => 'Apple Smartphone',
        ]);

        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Samsung S24',
            'slug' => 'samsung-s24',
            'sku' => 'SAMSUNGS24',
            'price' => 899.99,
            'quantity' => 15,
            'status' => ProductStatus::ACTIVE,
            'description' => 'Samsung Smartphone',
        ]);

        // Search name
        $response = $this->getJson('/api/v1/products?search=iPhone');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'iPhone 15');

        // Search description
        $response2 = $this->getJson('/api/v1/products?search=Smartphone');
        $response2->assertStatus(200)
                  ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_products_by_category()
    {
        $otherCategory = Category::create([
            'name' => 'Clothing',
            'slug' => 'clothing',
            'status' => CategoryStatus::ACTIVE,
        ]);

        Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        Product::create([
            'category_id' => $otherCategory->id,
            'name' => 'T-Shirt',
            'slug' => 't-shirt',
            'sku' => 'TSHIRT',
            'price' => 19.99,
            'quantity' => 50,
            'status' => ProductStatus::ACTIVE,
        ]);

        $response = $this->getJson("/api/v1/products?category_id={$this->category->id}");
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'iPhone 15');
    }

    public function test_can_filter_products_by_status()
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Samsung S24',
            'slug' => 'samsung-s24',
            'sku' => 'SAMSUNGS24',
            'price' => 899.99,
            'quantity' => 15,
            'status' => ProductStatus::DRAFT,
        ]);

        $response = $this->getJson('/api/v1/products?status=draft');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data')
                 ->assertJsonPath('data.0.name', 'Samsung S24');
    }

    public function test_can_sort_products_by_price()
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Cheap Phone',
            'slug' => 'cheap-phone',
            'sku' => 'CHEAP',
            'price' => 100.00,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        Product::create([
            'category_id' => $this->category->id,
            'name' => 'Expensive Phone',
            'slug' => 'expensive-phone',
            'sku' => 'EXPENSIVE',
            'price' => 1000.00,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        // Ascending
        $responseAsc = $this->getJson('/api/v1/products?sort=price');
        $responseAsc->assertStatus(200)
                    ->assertJsonPath('data.0.name', 'Cheap Phone')
                    ->assertJsonPath('data.1.name', 'Expensive Phone');

        // Descending
        $responseDesc = $this->getJson('/api/v1/products?sort=-price');
        $responseDesc->assertStatus(200)
                     ->assertJsonPath('data.0.name', 'Expensive Phone')
                     ->assertJsonPath('data.1.name', 'Cheap Phone');
    }

    public function test_admin_can_create_product()
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'description' => 'New Apple Phone',
            'price' => 999.99,
            'compare_at_price' => 1099.99,
            'stock' => 10,
            'status' => 'active',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'iPhone 15')
                 ->assertJsonPath('data.stock', 10)
                 ->assertJsonPath('data.status', 'active');
    }

    public function test_non_admin_cannot_create_product()
    {
        $customer = $this->getCustomerUser();

        $response = $this->actingAs($customer, 'sanctum')->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'stock' => 10,
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_guest_cannot_create_product()
    {
        $response = $this->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'stock' => 10,
            'status' => 'active',
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_create_product_with_duplicate_slug_or_sku()
    {
        Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        $admin = $this->getAdminUser();

        // Duplicate Slug
        $responseSlug = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15PRO',
            'price' => 1199.99,
            'stock' => 5,
            'status' => 'active',
        ]);

        $responseSlug->assertStatus(422)
                     ->assertJsonValidationErrors(['slug']);

        // Duplicate SKU
        $responseSku = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/products', [
            'category_id' => $this->category->id,
            'name' => 'iPhone 15 Pro',
            'slug' => 'iphone-15-pro',
            'sku' => 'IPHONE15',
            'price' => 1199.99,
            'stock' => 5,
            'status' => 'active',
        ]);

        $responseSku->assertStatus(422)
                    ->assertJsonValidationErrors(['sku']);
    }

    public function test_cannot_create_product_with_invalid_category()
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/products', [
            'category_id' => 9999, // Invalid Category ID
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'stock' => 10,
            'status' => 'active',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['category_id']);
    }

    public function test_can_show_product()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'iPhone 15');
    }

    public function test_admin_can_update_product()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/products/{$product->id}", [
            'category_id' => $this->category->id,
            'name' => 'iPhone 15 updated',
            'slug' => 'iphone-15-updated',
            'sku' => 'IPHONE15UPDATED',
            'price' => 1099.99,
            'stock' => 20,
            'status' => 'archived',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'iPhone 15 updated')
                 ->assertJsonPath('data.stock', 20)
                 ->assertJsonPath('data.status', 'archived');
    }

    public function test_admin_can_delete_product()
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
        ]);

        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
