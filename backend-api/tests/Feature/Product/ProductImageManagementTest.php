<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Enums\ProductStatus;
use App\Enums\CategoryStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductImageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions and roles
        $manageProductImages = Permission::firstOrCreate(['name' => 'manage-product-images', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($manageProductImages);

        // Create a default category and product
        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => CategoryStatus::ACTIVE,
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'iPhone 15',
            'slug' => 'iphone-15',
            'sku' => 'IPHONE15',
            'price' => 999.99,
            'quantity' => 10,
            'status' => ProductStatus::ACTIVE,
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

    public function test_can_list_product_images()
    {
        ProductImage::create([
            'product_id' => $this->product->id,
            'image_path' => 'product-images/test1.jpg',
            'is_primary' => true,
        ]);

        ProductImage::create([
            'product_id' => $this->product->id,
            'image_path' => 'product-images/test2.jpg',
            'is_primary' => false,
        ]);

        $response = $this->getJson("/api/v1/products/{$this->product->id}/images");

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_upload_product_image()
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($admin, 'sanctum')->postJson("/api/v1/products/{$this->product->id}/images", [
            'image' => $file,
            'is_primary' => true,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.is_primary', true);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $this->product->id,
            'is_primary' => true,
        ]);

        $image = ProductImage::first();
        Storage::disk('public')->assertExists($image->image_path);
    }

    public function test_non_admin_cannot_upload_product_image()
    {
        Storage::fake('public');
        $customer = $this->getCustomerUser();
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($customer, 'sanctum')->postJson("/api/v1/products/{$this->product->id}/images", [
            'image' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_product_image()
    {
        Storage::fake('public');
        $admin = $this->getAdminUser();
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('product-images', 'public');

        $image = ProductImage::create([
            'product_id' => $this->product->id,
            'image_path' => $path,
        ]);

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/products/{$this->product->id}/images/{$image->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_can_set_primary_image()
    {
        $admin = $this->getAdminUser();

        $image1 = ProductImage::create([
            'product_id' => $this->product->id,
            'image_path' => 'product-images/test1.jpg',
            'is_primary' => true,
        ]);

        $image2 = ProductImage::create([
            'product_id' => $this->product->id,
            'image_path' => 'product-images/test2.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/products/{$this->product->id}/images/{$image2->id}/primary");

        $response->assertStatus(200)
                 ->assertJsonPath('data.is_primary', true);

        $this->assertFalse($image1->refresh()->is_primary);
        $this->assertTrue($image2->refresh()->is_primary);
    }
}
