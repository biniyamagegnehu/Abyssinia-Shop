<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductImagePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $viewProductImages = Permission::firstOrCreate(['name' => 'view-product-images', 'guard_name' => 'web']);
        $manageProductImages = Permission::firstOrCreate(['name' => 'manage-product-images', 'guard_name' => 'web']);

        // Fetch roles
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        // Assign permissions
        $admin->givePermissionTo([$viewProductImages, $manageProductImages]);
        $staff->givePermissionTo([$viewProductImages]);
    }
}
