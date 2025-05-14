<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StoreOwner;
use App\Models\StoreStaff;
use Illuminate\Support\Facades\DB;

class ManagePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:manage 
                            {type : The type of user (store-owner or store-staff)}
                            {action : The action to perform (list, add, remove, reset)}
                            {--id= : The ID of the user}
                            {--permission= : The permission to add or remove}
                            {--role= : Filter by role (for store-staff only)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage permissions for store owners and store staff';

    /**
     * Available permissions
     * 
     * @var array
     */
    protected $availablePermissions = [
        'view-store', 'edit-store', 
        'view-products', 'manage-products', 'delete-products',
        'view-orders', 'manage-orders', 'delete-orders',
        'manage-staff'
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $type = $this->argument('type');
        $action = $this->argument('action');
        
        if (!in_array($type, ['store-owner', 'store-staff'])) {
            $this->error("Invalid type. Must be 'store-owner' or 'store-staff'");
            return 1;
        }
        
        if (!in_array($action, ['list', 'add', 'remove', 'reset'])) {
            $this->error("Invalid action. Must be 'list', 'add', 'remove', or 'reset'");
            return 1;
        }
        
        switch ($action) {
            case 'list':
                $this->listPermissions($type);
                break;
            case 'add':
                $this->addPermission($type);
                break;
            case 'remove':
                $this->removePermission($type);
                break;
            case 'reset':
                $this->resetPermissions($type);
                break;
        }
        
        return 0;
    }
    
    /**
     * List permissions for a user type
     * 
     * @param string $type
     * @return void
     */
    protected function listPermissions(string $type)
    {
        if ($type === 'store-owner') {
            $id = $this->option('id');
            
            if ($id) {
                $storeOwner = StoreOwner::find($id);
                
                if (!$storeOwner) {
                    $this->error("Store owner with ID {$id} not found");
                    return;
                }
                
                $this->info("Permissions for store owner ID {$id}:");
                $this->table(['Permission'], array_map(function($permission) {
                    return [$permission];
                }, $storeOwner->permissions ?? []));
            } else {
                $storeOwners = StoreOwner::all();
                
                $this->info("All store owners and their permissions:");
                $rows = [];
                
                foreach ($storeOwners as $storeOwner) {
                    $rows[] = [
                        $storeOwner->id,
                        $storeOwner->user->name ?? 'N/A',
                        implode(', ', $storeOwner->permissions ?? [])
                    ];
                }
                
                $this->table(['ID', 'Name', 'Permissions'], $rows);
            }
        } else { // store-staff
            $id = $this->option('id');
            $role = $this->option('role');
            
            if ($id) {
                $storeStaff = StoreStaff::find($id);
                
                if (!$storeStaff) {
                    $this->error("Store staff with ID {$id} not found");
                    return;
                }
                
                $this->info("Permissions for store staff ID {$id}:");
                $this->table(['Permission'], array_map(function($permission) {
                    return [$permission];
                }, $storeStaff->permissions ?? []));
            } else {
                $query = StoreStaff::query();
                
                if ($role) {
                    $query->where('role', $role);
                }
                
                $storeStaff = $query->get();
                
                $this->info("All store staff and their permissions:");
                $rows = [];
                
                foreach ($storeStaff as $staff) {
                    $rows[] = [
                        $staff->id,
                        $staff->user->name ?? 'N/A',
                        $staff->role,
                        implode(', ', $staff->permissions ?? [])
                    ];
                }
                
                $this->table(['ID', 'Name', 'Role', 'Permissions'], $rows);
            }
        }
    }
    
    /**
     * Add a permission to a user
     * 
     * @param string $type
     * @return void
     */
    protected function addPermission(string $type)
    {
        $id = $this->option('id');
        $permission = $this->option('permission');
        
        if (!$id) {
            $this->error("ID is required for adding permissions");
            return;
        }
        
        if (!$permission) {
            $this->error("Permission is required for adding permissions");
            return;
        }
        
        if (!in_array($permission, $this->availablePermissions)) {
            $this->error("Invalid permission. Available permissions are: " . implode(', ', $this->availablePermissions));
            return;
        }
        
        if ($type === 'store-owner') {
            $storeOwner = StoreOwner::find($id);
            
            if (!$storeOwner) {
                $this->error("Store owner with ID {$id} not found");
                return;
            }
            
            $permissions = $storeOwner->permissions ?? [];
            
            if (in_array($permission, $permissions)) {
                $this->info("Permission '{$permission}' already exists for store owner ID {$id}");
                return;
            }
            
            $permissions[] = $permission;
            $storeOwner->permissions = $permissions;
            $storeOwner->save();
            
            $this->info("Permission '{$permission}' added to store owner ID {$id}");
        } else { // store-staff
            $storeStaff = StoreStaff::find($id);
            
            if (!$storeStaff) {
                $this->error("Store staff with ID {$id} not found");
                return;
            }
            
            $permissions = $storeStaff->permissions ?? [];
            
            if (in_array($permission, $permissions)) {
                $this->info("Permission '{$permission}' already exists for store staff ID {$id}");
                return;
            }
            
            $permissions[] = $permission;
            $storeStaff->permissions = $permissions;
            $storeStaff->save();
            
            $this->info("Permission '{$permission}' added to store staff ID {$id}");
        }
    }
    
    /**
     * Remove a permission from a user
     * 
     * @param string $type
     * @return void
     */
    protected function removePermission(string $type)
    {
        $id = $this->option('id');
        $permission = $this->option('permission');
        
        if (!$id) {
            $this->error("ID is required for removing permissions");
            return;
        }
        
        if (!$permission) {
            $this->error("Permission is required for removing permissions");
            return;
        }
        
        if ($type === 'store-owner') {
            $storeOwner = StoreOwner::find($id);
            
            if (!$storeOwner) {
                $this->error("Store owner with ID {$id} not found");
                return;
            }
            
            $permissions = $storeOwner->permissions ?? [];
            
            if (!in_array($permission, $permissions)) {
                $this->info("Permission '{$permission}' does not exist for store owner ID {$id}");
                return;
            }
            
            $permissions = array_diff($permissions, [$permission]);
            $storeOwner->permissions = $permissions;
            $storeOwner->save();
            
            $this->info("Permission '{$permission}' removed from store owner ID {$id}");
        } else { // store-staff
            $storeStaff = StoreStaff::find($id);
            
            if (!$storeStaff) {
                $this->error("Store staff with ID {$id} not found");
                return;
            }
            
            $permissions = $storeStaff->permissions ?? [];
            
            if (!in_array($permission, $permissions)) {
                $this->info("Permission '{$permission}' does not exist for store staff ID {$id}");
                return;
            }
            
            $permissions = array_diff($permissions, [$permission]);
            $storeStaff->permissions = $permissions;
            $storeStaff->save();
            
            $this->info("Permission '{$permission}' removed from store staff ID {$id}");
        }
    }
    
    /**
     * Reset permissions for a user
     * 
     * @param string $type
     * @return void
     */
    protected function resetPermissions(string $type)
    {
        $id = $this->option('id');
        $role = $this->option('role');
        
        if (!$id && !$role) {
            $this->error("Either ID or role is required for resetting permissions");
            return;
        }
        
        if ($type === 'store-owner') {
            if ($id) {
                $storeOwner = StoreOwner::find($id);
                
                if (!$storeOwner) {
                    $this->error("Store owner with ID {$id} not found");
                    return;
                }
                
                // Default permissions for store owners
                $defaultPermissions = [
                    'view-store', 'edit-store', 
                    'manage-products', 
                    'manage-orders', 
                    'manage-staff'
                ];
                
                $storeOwner->permissions = $defaultPermissions;
                $storeOwner->save();
                
                $this->info("Permissions reset to default for store owner ID {$id}");
            } else {
                $this->error("ID is required for resetting store owner permissions");
                return;
            }
        } else { // store-staff
            if ($id) {
                $storeStaff = StoreStaff::find($id);
                
                if (!$storeStaff) {
                    $this->error("Store staff with ID {$id} not found");
                    return;
                }
                
                // Reset permissions based on role
                $this->resetStaffPermissionsByRole($storeStaff, $storeStaff->role);
                
                $this->info("Permissions reset to default for store staff ID {$id}");
            } elseif ($role) {
                // Reset permissions for all staff with the given role
                $storeStaff = StoreStaff::where('role', $role)->get();
                
                if ($storeStaff->isEmpty()) {
                    $this->error("No store staff found with role '{$role}'");
                    return;
                }
                
                foreach ($storeStaff as $staff) {
                    $this->resetStaffPermissionsByRole($staff, $role);
                }
                
                $this->info("Permissions reset to default for all store staff with role '{$role}'");
            }
        }
    }
    
    /**
     * Reset permissions for a staff member based on their role
     * 
     * @param StoreStaff $staff
     * @param string $role
     * @return void
     */
    protected function resetStaffPermissionsByRole(StoreStaff $staff, string $role)
    {
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
        
        if (isset($rolePermissions[$role])) {
            $staff->permissions = $rolePermissions[$role];
            $staff->save();
        }
    }
}
