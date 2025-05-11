<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $storeOwnerRole = Role::firstOrCreate(['name' => 'store-owner']);
        $storeStaffRole = Role::firstOrCreate(['name' => 'store-staff']);

        // Create global permissions
        $permissions = [
            // Admin permissions
            'manage-all-stores',
            'manage-users',
            'view-reports',
            'manage-settings',
            
            // Module permissions (not store-specific)
            'manage-products',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            
            'manage-orders',
            'view-orders',
            'create-orders',
            'edit-orders',
            'delete-orders',
            'update-order-status',
            'update-payment-status',
            
            'manage-stores',
            'view-stores',
            'create-stores',
            'edit-stores',
            'delete-stores',
        ];
        
        // Create all global permissions
        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[] = Permission::firstOrCreate(['name' => $permission]);
        }

        // Create store-specific permissions
        $storePermissionMap = [];
        $stores = Store::all();
        
        foreach ($stores as $store) {
            $storeId = $store->id;
            $storePermissionMap[$storeId] = [];
            
            // Store-specific permissions
            $storePermissions = [
                // Store owner permissions
                'manage-store-' . $storeId,
                
                // Store staff permissions
                'access-store-' . $storeId,
                'view-store-' . $storeId,
                'create-store-' . $storeId,
                'edit-store-' . $storeId,
                'delete-store-' . $storeId,
                
                // Product permissions for specific store
                'manage-products-store-' . $storeId,
                'view-products-store-' . $storeId,
                'create-products-store-' . $storeId,
                'edit-products-store-' . $storeId,
                'delete-products-store-' . $storeId,
                
                // Order permissions for specific store
                'manage-orders-store-' . $storeId,
                'view-orders-store-' . $storeId,
                'create-orders-store-' . $storeId,
                'edit-orders-store-' . $storeId,
                'delete-orders-store-' . $storeId,
                'update-order-status-store-' . $storeId,
                'update-payment-status-store-' . $storeId,
            ];
            
            foreach ($storePermissions as $permission) {
                $storePermissionMap[$storeId][$permission] = Permission::firstOrCreate(['name' => $permission]);
                $createdPermissions[] = $storePermissionMap[$storeId][$permission];
            }
        }

        // Give all permissions to admin role
        $adminRole->syncPermissions($createdPermissions);

        // Assign permissions to existing users based on their current roles
        $this->assignPermissionsToExistingUsers($storePermissionMap);
    }

    /**
     * Assign permissions to existing users based on their current roles
     * 
     * @param array $storePermissionMap Map of store permissions by store ID
     */
    private function assignPermissionsToExistingUsers(array $storePermissionMap): void
    {
        // Get roles
        $adminRole = Role::findByName('admin');
        $storeOwnerRole = Role::findByName('store-owner');
        $storeStaffRole = Role::findByName('store-staff');

        // Assign admin role to users in the admins table
        $adminUsers = \DB::table('admins')->pluck('user_id');
        foreach ($adminUsers as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->syncRoles([$adminRole]);
            }
        }

        // Assign store owner role and permissions to users in the store_owners table
        $storeOwners = \DB::table('store_owners')->get();
        foreach ($storeOwners as $storeOwner) {
            $user = User::find($storeOwner->user_id);
            if ($user) {
                $user->assignRole($storeOwnerRole);
                
                // Give store owner all permissions for their store
                $storeId = $storeOwner->store_id;
                if (isset($storePermissionMap[$storeId])) {
                    $storePermissions = [
                        'manage-store-' . $storeId,
                        'access-store-' . $storeId,
                        'view-store-' . $storeId,
                        'edit-store-' . $storeId,
                        'manage-products-store-' . $storeId,
                        'view-products-store-' . $storeId,
                        'create-products-store-' . $storeId,
                        'edit-products-store-' . $storeId,
                        'delete-products-store-' . $storeId,
                        'manage-orders-store-' . $storeId,
                        'view-orders-store-' . $storeId,
                        'create-orders-store-' . $storeId,
                        'edit-orders-store-' . $storeId,
                        'update-order-status-store-' . $storeId,
                        'update-payment-status-store-' . $storeId,
                    ];
                    
                    $permissions = [];
                    foreach ($storePermissions as $permission) {
                        if (isset($storePermissionMap[$storeId][$permission])) {
                            $permissions[] = $storePermissionMap[$storeId][$permission];
                        }
                    }
                    
                    $user->syncPermissions($permissions);
                }
            }
        }

        // Assign store staff role and permissions to users in the store_staff table
        $storeStaffMembers = \DB::table('store_staff')->get();
        foreach ($storeStaffMembers as $staff) {
            $user = User::find($staff->user_id);
            if ($user) {
                $user->assignRole($storeStaffRole);
                $storeId = $staff->store_id;
                
                if (!isset($storePermissionMap[$storeId])) {
                    continue;
                }
                
                // Give basic store access permission
                $userPermissions = [];
                if (isset($storePermissionMap[$storeId]['access-store-' . $storeId])) {
                    $userPermissions[] = $storePermissionMap[$storeId]['access-store-' . $storeId];
                }
                
                // Parse permissions from JSON
                $staffPermissions = json_decode($staff->permissions, true) ?? [];
                
                // Map old permissions to new permission format
                $permissionMap = [
                    'view' => [
                        'view-store-' . $storeId,
                        'view-products-store-' . $storeId,
                        'view-orders-store-' . $storeId,
                    ],
                    'create' => [
                        'create-products-store-' . $storeId,
                        'create-orders-store-' . $storeId,
                    ],
                    'edit' => [
                        'edit-store-' . $storeId,
                        'edit-products-store-' . $storeId,
                        'edit-orders-store-' . $storeId,
                        'update-order-status-store-' . $storeId,
                        'update-payment-status-store-' . $storeId,
                    ],
                    'delete' => [
                        'delete-products-store-' . $storeId,
                    ],
                ];
                
                foreach ($staffPermissions as $permission) {
                    if (isset($permissionMap[$permission])) {
                        foreach ($permissionMap[$permission] as $spatiePermission) {
                            if (isset($storePermissionMap[$storeId][$spatiePermission])) {
                                $userPermissions[] = $storePermissionMap[$storeId][$spatiePermission];
                            }
                        }
                    }
                }
                
                $user->syncPermissions($userPermissions);
            }
        }
    }
}
