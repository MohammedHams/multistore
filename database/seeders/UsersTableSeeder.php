<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Store;
use App\Models\StoreOwner;
use App\Models\StoreStaff;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin_new@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create Admin record
        Admin::create([
            'user_id' => $adminUser->id,
        ]);

        // Create Store
        $store = Store::create([
            'name' => 'Demo Store',
            'domain' => 'demo-store',
            'email' => 'store@example.com',
            'phone' => '123-456-7890',
            'is_active' => true,
            'settings' => json_encode([
                'currency' => 'USD',
                'timezone' => 'UTC',
            ]),
        ]);

        // Create Store Owner User
        $storeOwnerUser = User::create([
            'name' => 'Store Owner',
            'email' => 'owner_new@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create Store Owner record
        StoreOwner::create([
            'user_id' => $storeOwnerUser->id,
            'store_id' => $store->id,
            'permissions' => json_encode([
                'view-store', 'edit-store', 
                'manage-products', 
                'manage-orders', 
                'manage-staff'
            ]),
        ]);

        // Create Store Staff User (Manager)
        $staffManagerUser = User::create([
            'name' => 'Store Manager',
            'email' => 'manager_new@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create Store Staff record (Manager)
        StoreStaff::create([
            'user_id' => $staffManagerUser->id,
            'store_id' => $store->id,
            'role' => 'manager',
            'permissions' => json_encode([
                'view-store', 'edit-store', 
                'view-products', 'manage-products', 
                'view-orders', 'manage-orders', 
                'manage-staff'
            ]),
        ]);

        // Create Store Staff User (Cashier)
        $staffCashierUser = User::create([
            'name' => 'Store Cashier',
            'email' => 'cashier_new@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create Store Staff record (Cashier)
        StoreStaff::create([
            'user_id' => $staffCashierUser->id,
            'store_id' => $store->id,
            'role' => 'cashier',
            'permissions' => json_encode([
                'view-store', 
                'view-products', 
                'view-orders', 'manage-orders'
            ]),
        ]);

        // Create Store Staff User (Inventory)
        $staffInventoryUser = User::create([
            'name' => 'Inventory Staff',
            'email' => 'inventory_new@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create Store Staff record (Inventory)
        StoreStaff::create([
            'user_id' => $staffInventoryUser->id,
            'store_id' => $store->id,
            'role' => 'inventory',
            'permissions' => json_encode([
                'view-store', 
                'view-products', 'manage-products', 
                'view-orders'
            ]),
        ]);

        $this->command->info('Users seeded successfully!');
    }
}
