<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the guard
        $guard = 'admin';

        // Create or find Super Admin role for the admin guard
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => $guard]
        );

        // Create all permissions (if you want to attach permissions to this role)
        $permissions = Permission::get();
        $superAdminRole->syncPermissions($permissions);

        // Create a user with the admin guard and assign Super Admin role
        $superAdmin = Admin::firstOrCreate([
            'email' => 'admin@trywebsight.com',
        ], [
            'name' => 'Super Admin',
            'password' => bcrypt('123456789')  // Change this to a secure password
        ]);

        // Assign the Super Admin role to the user
        $superAdmin->assignRole($superAdminRole);
    }
}
