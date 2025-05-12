<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use App\Models\StoreStaff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StoreOwnerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure the store-staff role exists
        $staffRole = Role::firstOrCreate(['name' => 'store-staff']);

        // 2. Create or get a store
        $store = Store::firstOrCreate(
            ['domain' => 'example-store.com'],
            [
                'name' => 'Example Store',
                'email' => 'store@example.com',
                'phone' => '1234567890',
                'is_active' => true,
                'settings' => json_encode(['currency' => 'USD']),
            ]
        );

        $storeId = $store->id;

        // 3. Define store-specific permissions
        $permissions = [
            "access-store-$storeId",
            "view-products-store-$storeId",
            "create-products-store-$storeId",
            "edit-products-store-$storeId",
        ];

        // 4. Create permissions if they don't exist
        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web',
            ]);
        }

        // 5. Create multiple staff users
        for ($i = 1; $i <= 3; $i++) {
            $email = "staff$i@example.com";
            $name = "Store Staff $i";

            $staff = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'), // Change in production
                    'email_verified_at' => now(),
                ]
            );

            // Assign role and permissions
            $staff->assignRole('store-staff');
            $staff->givePermissionTo($permissions);

            // Optional: Link to store_staff table
            if (class_exists(StoreStaff::class)) {
                StoreStaff::firstOrCreate([
                    'user_id' => $staff->id,
                    'store_id' => $storeId,
                ]);
            }

            // Output info for each user
            $this->command->info("✅ Created: $name ($email)");
        }

        $this->command->info("🏬 Store ID: $storeId linked to all staff.");
    }
}
