<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup permissions and roles
        $manageCategories = Permission::firstOrCreate(['name' => 'manage-categories', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($manageCategories);
    }

    protected function getAdminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Admin');
        return $user;
    }

    public function test_can_list_categories()
    {
        Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'status' => 'active']);
        
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_create_category()
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/categories', [
            'name' => 'Laptops',
            'slug' => 'laptops',
            'status' => 'active',
            'description' => 'Laptop computers',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Laptops');
    }

    public function test_cannot_create_with_duplicate_slug()
    {
        Category::create(['name' => 'Tech', 'slug' => 'tech', 'status' => 'active']);
        
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/categories', [
            'name' => 'Another Tech',
            'slug' => 'tech',
            'status' => 'active',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['slug']);
    }

    public function test_cannot_assign_category_to_itself()
    {
        $category = Category::create(['name' => 'Tech', 'slug' => 'tech', 'status' => 'active']);
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Tech',
            'slug' => 'tech',
            'status' => 'active',
            'parent_id' => $category->id,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_cannot_assign_category_to_descendant()
    {
        $parent = Category::create(['name' => 'Parent', 'slug' => 'parent', 'status' => 'active']);
        $child = Category::create(['name' => 'Child', 'slug' => 'child', 'status' => 'active', 'parent_id' => $parent->id]);
        
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->putJson("/api/v1/categories/{$parent->id}", [
            'name' => 'Parent',
            'slug' => 'parent',
            'status' => 'active',
            'parent_id' => $child->id, // Trying to make the parent a child of its own child
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['parent_id']);
    }

    public function test_cannot_delete_category_with_children()
    {
        $parent = Category::create(['name' => 'Parent', 'slug' => 'parent', 'status' => 'active']);
        Category::create(['name' => 'Child', 'slug' => 'child', 'status' => 'active', 'parent_id' => $parent->id]);
        
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/categories/{$parent->id}");

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['category']);
    }
}
