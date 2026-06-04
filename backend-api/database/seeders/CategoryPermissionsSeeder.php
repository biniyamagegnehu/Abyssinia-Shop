<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CategoryPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $viewCategories = Permission::firstOrCreate(['name' => 'view-categories', 'guard_name' => 'web']);
        $manageCategories = Permission::firstOrCreate(['name' => 'manage-categories', 'guard_name' => 'web']);

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $staff = Role::firstOrCreate(['name' => 'Staff', 'guard_name' => 'web']);

        // Assign permissions
        $admin->givePermissionTo([$viewCategories, $manageCategories]);
        $staff->givePermissionTo([$viewCategories]);
    }
}
