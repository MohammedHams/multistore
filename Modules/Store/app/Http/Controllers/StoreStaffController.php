<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store as StoreModel;
use App\Models\StoreStaff as StoreStaffModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\Store\Entities\Store;
use Modules\Store\Entities\StoreStaff;
use Modules\Store\app\Repositories\Interfaces\StoreRepositoryInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class StoreStaffController extends Controller
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * StoreStaffController constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Display a listing of the store staff.
     *
     * @param StoreModel $store
     * @return \Illuminate\View\View
     */
    public function index(StoreModel $store)
    {
        try {
            $staff = $this->storeRepository->getStoreStaff($store->id);
            
            return view('store::staff.index', compact('store', 'staff'));
        } catch (\Exception $e) {
            Log::error('Error fetching store staff: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while fetching store staff. Please try again.'));
        }
    }

    /**
     * Show the form for adding a new staff member to the store.
     *
     * @param StoreModel $store
     * @return \Illuminate\View\View
     */
    public function create(StoreModel $store)
    {
        try {
            // Get users who are not already staff of this store and who don't have the store-staff role
            $storeStaffIds = StoreStaffModel::where('store_id', $store->id)->pluck('user_id')->toArray();
            
            // Get users who don't have the store-staff role for this store
            $storeStaffRole = Role::findByName('store-staff');
            $usersWithRole = User::role('store-staff')->get();
            $usersWithRoleForThisStore = [];
            
            foreach ($usersWithRole as $user) {
                if ($user->hasPermissionTo('access-store-' . $store->id)) {
                    $usersWithRoleForThisStore[] = $user->id;
                }
            }
            
            $excludeUserIds = array_merge($storeStaffIds, $usersWithRoleForThisStore);
            $availableUsers = User::whereNotIn('id', $excludeUserIds)->get();
            
            // Get available permissions for this store
            $availablePermissions = [
                'view' => [
                    'label' => __('View'),
                    'description' => __('Can view store information, products, and orders'),
                    'permissions' => [
                        'view-store-' . $store->id,
                        'view-products-store-' . $store->id,
                        'view-orders-store-' . $store->id,
                    ],
                ],
                'create' => [
                    'label' => __('Create'),
                    'description' => __('Can create new products and orders'),
                    'permissions' => [
                        'create-products-store-' . $store->id,
                        'create-orders-store-' . $store->id,
                    ],
                ],
                'edit' => [
                    'label' => __('Edit'),
                    'description' => __('Can edit store information, products, orders, and update statuses'),
                    'permissions' => [
                        'edit-store-' . $store->id,
                        'edit-products-store-' . $store->id,
                        'edit-orders-store-' . $store->id,
                        'update-order-status-store-' . $store->id,
                        'update-payment-status-store-' . $store->id,
                    ],
                ],
                'delete' => [
                    'label' => __('Delete'),
                    'description' => __('Can delete products'),
                    'permissions' => [
                        'delete-products-store-' . $store->id,
                    ],
                ],
            ];
            
            return view('store::staff.create', compact('store', 'availableUsers', 'availablePermissions'));
        } catch (\Exception $e) {
            Log::error('Error loading staff creation form: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while loading the form. Please try again.'));
        }
    }

    /**
     * Store a newly created staff member in the store.
     *
     * @param Request $request
     * @param StoreModel $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, StoreModel $store)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'string']
        ]);
        
        try {
            $userId = $request->input('user_id');
            $permissionGroups = $request->input('permissions', []);
            
            // Get the user
            $user = User::findOrFail($userId);
            
            // Assign the store-staff role to the user
            $user->assignRole('store-staff');
            
            // Give the user access to this store
            $user->givePermissionTo('access-store-' . $store->id);
            
            // Map the permission groups to actual permissions
            $availablePermissions = [
                'view' => [
                    'view-store-' . $store->id,
                    'view-products-store-' . $store->id,
                    'view-orders-store-' . $store->id,
                ],
                'create' => [
                    'create-products-store-' . $store->id,
                    'create-orders-store-' . $store->id,
                ],
                'edit' => [
                    'edit-store-' . $store->id,
                    'edit-products-store-' . $store->id,
                    'edit-orders-store-' . $store->id,
                    'update-order-status-store-' . $store->id,
                    'update-payment-status-store-' . $store->id,
                ],
                'delete' => [
                    'delete-products-store-' . $store->id,
                ],
            ];
            
            $permissions = [];
            foreach ($permissionGroups as $group) {
                if (isset($availablePermissions[$group])) {
                    $permissions = array_merge($permissions, $availablePermissions[$group]);
                }
            }
            
            // Assign permissions to the user
            foreach ($permissions as $permission) {
                $user->givePermissionTo($permission);
            }
            
            // For backward compatibility, also add the staff to the store_staff table
            $this->storeRepository->addStaff(
                $store->id, 
                $userId, 
                $permissionGroups
            );
            
            return redirect()->route('store.staff.index', $store)
                ->with('success', __('Staff member added to store successfully.'));
        } catch (\Exception $e) {
            Log::error('Error adding staff to store: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while adding the staff member. Please try again.'))
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified staff member's permissions.
     *
     * @param StoreModel $store
     * @param User $user
     * @return \Illuminate\View\View
     */
    public function edit(StoreModel $store, User $user)
    {
        try {
            // Check if the user has access to this store
            if (!$user->hasPermissionTo('access-store-' . $store->id)) {
                return redirect()->route('store.staff.index', $store)
                    ->with('error', __('This user is not a staff member of this store.'));
            }
            
            // Get available permissions for this store
            $availablePermissions = [
                'view' => [
                    'label' => __('View'),
                    'description' => __('Can view store information, products, and orders'),
                    'permissions' => [
                        'view-store-' . $store->id,
                        'view-products-store-' . $store->id,
                        'view-orders-store-' . $store->id,
                    ],
                ],
                'create' => [
                    'label' => __('Create'),
                    'description' => __('Can create new products and orders'),
                    'permissions' => [
                        'create-products-store-' . $store->id,
                        'create-orders-store-' . $store->id,
                    ],
                ],
                'edit' => [
                    'label' => __('Edit'),
                    'description' => __('Can edit store information, products, orders, and update statuses'),
                    'permissions' => [
                        'edit-store-' . $store->id,
                        'edit-products-store-' . $store->id,
                        'edit-orders-store-' . $store->id,
                        'update-order-status-store-' . $store->id,
                        'update-payment-status-store-' . $store->id,
                    ],
                ],
                'delete' => [
                    'label' => __('Delete'),
                    'description' => __('Can delete products'),
                    'permissions' => [
                        'delete-products-store-' . $store->id,
                    ],
                ],
            ];
            
            // Determine which permission groups the user has
            $currentPermissions = [];
            foreach ($availablePermissions as $group => $details) {
                $hasAllPermissions = true;
                foreach ($details['permissions'] as $permission) {
                    if (!$user->hasPermissionTo($permission)) {
                        $hasAllPermissions = false;
                        break;
                    }
                }
                if ($hasAllPermissions) {
                    $currentPermissions[] = $group;
                }
            }
            
            return view('store::staff.edit', compact('store', 'user', 'currentPermissions', 'availablePermissions'));
        } catch (\Exception $e) {
            Log::error('Error loading staff edit form: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while loading the form. Please try again.'));
        }
    }

    /**
     * Update the specified staff member's permissions in the store.
     *
     * @param Request $request
     * @param StoreModel $store
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, StoreModel $store, User $user)
    {
        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'string']
        ]);
        
        try {
            $permissionGroups = $request->input('permissions', []);
            
            // Map the permission groups to actual permissions
            $availablePermissions = [
                'view' => [
                    'view-store-' . $store->id,
                    'view-products-store-' . $store->id,
                    'view-orders-store-' . $store->id,
                ],
                'create' => [
                    'create-products-store-' . $store->id,
                    'create-orders-store-' . $store->id,
                ],
                'edit' => [
                    'edit-store-' . $store->id,
                    'edit-products-store-' . $store->id,
                    'edit-orders-store-' . $store->id,
                    'update-order-status-store-' . $store->id,
                    'update-payment-status-store-' . $store->id,
                ],
                'delete' => [
                    'delete-products-store-' . $store->id,
                ],
            ];
            
            // Get all permissions related to this store
            $allStorePermissions = [];
            foreach ($availablePermissions as $group => $permissions) {
                $allStorePermissions = array_merge($allStorePermissions, $permissions);
            }
            
            // Remove all existing store-specific permissions
            foreach ($allStorePermissions as $permission) {
                if ($user->hasPermissionTo($permission)) {
                    $user->revokePermissionTo($permission);
                }
            }
            
            // Always keep the basic access permission
            if (!$user->hasPermissionTo('access-store-' . $store->id)) {
                $user->givePermissionTo('access-store-' . $store->id);
            }
            
            // Add the new permissions
            $newPermissions = [];
            foreach ($permissionGroups as $group) {
                if (isset($availablePermissions[$group])) {
                    $newPermissions = array_merge($newPermissions, $availablePermissions[$group]);
                }
            }
            
            // Assign permissions to the user
            foreach ($newPermissions as $permission) {
                $user->givePermissionTo($permission);
            }
            
            // For backward compatibility, also update the store_staff table
            $this->storeRepository->updateStaffPermissions(
                $store->id, 
                $user->id, 
                $permissionGroups
            );
            
            return redirect()->route('store.staff.index', $store)
                ->with('success', __('Staff permissions updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Error updating staff permissions: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while updating the staff permissions. Please try again.'))
                ->withInput();
        }
    }

    /**
     * Remove the specified staff member from the store.
     *
     * @param StoreModel $store
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(StoreModel $store, User $user)
    {
        try {
            // Get all permissions related to this store
            $storePermissions = [
                'access-store-' . $store->id,
                'view-store-' . $store->id,
                'create-store-' . $store->id,
                'edit-store-' . $store->id,
                'delete-store-' . $store->id,
                'manage-products-store-' . $store->id,
                'view-products-store-' . $store->id,
                'create-products-store-' . $store->id,
                'edit-products-store-' . $store->id,
                'delete-products-store-' . $store->id,
                'manage-orders-store-' . $store->id,
                'view-orders-store-' . $store->id,
                'create-orders-store-' . $store->id,
                'edit-orders-store-' . $store->id,
                'delete-orders-store-' . $store->id,
                'update-order-status-store-' . $store->id,
                'update-payment-status-store-' . $store->id,
            ];
            
            // Remove all store-specific permissions from the user
            foreach ($storePermissions as $permission) {
                if ($user->hasPermissionTo($permission)) {
                    $user->revokePermissionTo($permission);
                }
            }
            
            // Check if the user has any other store permissions
            $hasOtherStorePermissions = false;
            $allStores = StoreModel::where('id', '!=', $store->id)->get();
            foreach ($allStores as $otherStore) {
                if ($user->hasPermissionTo('access-store-' . $otherStore->id)) {
                    $hasOtherStorePermissions = true;
                    break;
                }
            }
            
            // If the user doesn't have permissions for any other store, remove the store-staff role
            if (!$hasOtherStorePermissions) {
                $user->removeRole('store-staff');
            }
            
            // For backward compatibility, also remove from the store_staff table
            $this->storeRepository->removeStaff($store->id, $user->id);
            
            return redirect()->route('store.staff.index', $store)
                ->with('success', __('Staff member removed from store successfully.'));
        } catch (\Exception $e) {
            Log::error('Error removing staff from store: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while removing the staff member. Please try again.'));
        }
    }
}
