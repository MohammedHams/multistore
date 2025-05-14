<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\StoreOwner;

class StoreOwnerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define default permissions for store owners
        $storeOwnerPermissions = [
            'view-store', 'edit-store', 
            'manage-products', 
            'manage-orders', 
            'manage-staff'
        ];

        // Get all store owners
        $storeOwners = StoreOwner::all();

        // Update each store owner with default permissions
        foreach ($storeOwners as $storeOwner) {
            $storeOwner->permissions = json_encode($storeOwnerPermissions);
            $storeOwner->save();
        }

        $this->command->info('Store owner permissions have been set.');
    }
}
