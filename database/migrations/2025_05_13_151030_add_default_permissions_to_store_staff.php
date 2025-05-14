<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDefaultPermissionsToStoreStaff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Define default permissions for each role
        $rolePermissions = [
            'manager' => [
                'view-store', 'edit-store', 
                'view-products', 'manage-products', 'delete-products',
                'view-orders', 'manage-orders', 'delete-orders',
                'manage-staff'
            ],
            'cashier' => [
                'view-store', 
                'view-products',
                'view-orders', 'manage-orders'
            ],
            'inventory' => [
                'view-store', 
                'view-products', 'manage-products',
                'view-orders'
            ],
            'sales' => [
                'view-store', 
                'view-products',
                'view-orders', 'manage-orders'
            ],
            'customer_service' => [
                'view-store', 
                'view-products',
                'view-orders'
            ]
        ];

        // First, check if the role column exists
        if (Schema::hasColumn('store_staff', 'role')) {
            // Update existing staff with default permissions based on their role
            foreach ($rolePermissions as $role => $permissions) {
                DB::table('store_staff')
                    ->where('role', $role)
                    ->update(['permissions' => json_encode($permissions)]);
            }
        }
        // If role column doesn't exist yet, we'll just skip this step
        // The permissions will be updated after the role column is added
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reset permissions to empty arrays
        DB::table('store_staff')
            ->update(['permissions' => json_encode([])]);
    }
}
