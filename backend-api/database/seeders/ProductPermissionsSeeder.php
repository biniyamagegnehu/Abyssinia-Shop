<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $viewProducts = Permission::firstOrCreate(['name' => 'view-products', 'guard_name' => 'web']);
        $manageProducts = Permission::firstOrCreate(['name' => 'manage-products', 'guard_name' => 'web']);

        // Fetch roles
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        // Assign permissions
        $admin->givePermissionTo([$viewProducts, $manageProducts]);
        $staff->givePermissionTo([$viewProducts]);
    }
}
