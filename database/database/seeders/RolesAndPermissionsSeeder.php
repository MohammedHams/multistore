<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\Admin;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'manage settings',
            'manage content'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create admin role and assign all permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Create regular user role with basic permissions
        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo('view dashboard');

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'), // Change in production!
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('admin');

        // Create admin record in admins table
        Admin::firstOrCreate(
            ['user_id' => $adminUser->id],
            [
                // Add any additional admin fields here
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create regular test user
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $testUser->assignRole('user');
    }
}
