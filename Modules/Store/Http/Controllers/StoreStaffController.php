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
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;
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
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function index($storeId)
    {
        try {
            Log::info('Accessing staff index for store ID: ' . $storeId);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            $staff = $this->storeRepository->getStoreStaff($storeId);
            
            return view('store::admin.staff.index', compact('store', 'staff'));
        } catch (\Exception $e) {
            Log::error('Error in StoreStaffController@index: ' . $e->getMessage());
            return redirect()->route('admin.store.index')->with('error', 'An error occurred while loading the staff list. Please try again.');
        }
    }

    /**
     * Show the form for adding a new store staff member.
     *
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function create($storeId)
    {
        try {
            // Log the store ID for debugging
            Log::info('Attempting to find store with ID: ' . $storeId);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            // Log successful store retrieval
            Log::info('Store found: ' . $store->name . ' (ID: ' . $store->id . ')');
            
            $availableUsers = User::whereDoesntHave('storeStaff', function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })->get();
            
            $roles = Role::where('guard_name', 'store-staff')->get();
            
            if ($roles->isEmpty()) {
                // Fallback to web guard if no store-staff roles exist
                $roles = Role::where('guard_name', 'web')->get();
            }
            
            // Define available permissions
            $availablePermissions = [
                'view_store' => [
                    'label' => 'View Store',
                    'description' => 'Can view store details'
                ],
                'edit_store' => [
                    'label' => 'Edit Store',
                    'description' => 'Can edit store details'
                ],
                'view_products' => [
                    'label' => 'View Products',
                    'description' => 'Can view store products'
                ],
                'manage_products' => [
                    'label' => 'Manage Products',
                    'description' => 'Can add, edit, and update products'
                ],
                'delete_products' => [
                    'label' => 'Delete Products',
                    'description' => 'Can delete products'
                ],
                'view_orders' => [
                    'label' => 'View Orders',
                    'description' => 'Can view store orders'
                ],
                'manage_orders' => [
                    'label' => 'Manage Orders',
                    'description' => 'Can process and update orders'
                ],
                'delete_orders' => [
                    'label' => 'Delete Orders',
                    'description' => 'Can delete orders'
                ],
                'manage_staff' => [
                    'label' => 'Manage Staff',
                    'description' => 'Can manage store staff'
                ]
            ];
            
            return view('store::admin.staff.create', compact('store', 'availableUsers', 'roles', 'availablePermissions'));
        } catch (\Exception $e) {
            Log::error('Error in StoreStaffController@create: ' . $e->getMessage());
            return redirect()->route('admin.store.index')->with('error', 'An error occurred while loading the staff creation page. Please try again.');
        }
    }

    /**
     * Store a newly created store staff member in storage.
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $storeId)
    {
        try {
            Log::info('Creating new staff for store ID: ' . $storeId);
            
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'role_id' => 'required|exists:roles,id',
                'permissions' => 'nullable|array',
            ]);
            
            // Check if store exists
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            $userId = $request->input('user_id');
            $roleId = $request->input('role_id');
            $permissions = $request->input('permissions', []);
            
            Log::info('Adding staff with user ID: ' . $userId . ', role ID: ' . $roleId);
            
            $result = $this->storeRepository->addStoreStaff(
                $storeId,
                $userId,
                $roleId,
                $permissions
            );
            
            if (!$result) {
                Log::warning('Failed to add staff member to store ID: ' . $storeId);
                return redirect()->back()->with('error', 'Failed to add staff member. Please try again.');
            }
            
            return redirect()->route('admin.store.staff.index', $storeId)->with('success', 'Staff member added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding store staff: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add staff member. Please try again.');
        }
    }

    /**
     * Display the specified store staff member.
     *
     * @param int $storeId
     * @param int $staffId
     * @return \Illuminate\View\View
     */
    public function show($storeId, $staffId)
    {
        try {
            Log::info('Viewing staff member ID: ' . $staffId . ' for store ID: ' . $storeId);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            $staff = $this->storeRepository->findStaffById($storeId, $staffId);
            
            if (!$staff) {
                Log::warning('Staff member not found with ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            return view('store::admin.staff.show', compact('store', 'staff'));
        } catch (\Exception $e) {
            Log::error('Error in StoreStaffController@show: ' . $e->getMessage());
            return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'An error occurred while viewing the staff member. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified store staff member.
     *
     * @param int $storeId
     * @param int $staffId
     * @return \Illuminate\View\View
     */
    public function edit($storeId, $staffId)
    {
        try {
            Log::info('Editing staff member ID: ' . $staffId . ' for store ID: ' . $storeId);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            $staff = $this->storeRepository->findStaffById($storeId, $staffId);
            
            if (!$staff) {
                Log::warning('Staff member not found with ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            $roles = Role::where('guard_name', 'store-staff')->get();
            
            if ($roles->isEmpty()) {
                // Fallback to web guard if no store-staff roles exist
                $roles = Role::where('guard_name', 'web')->get();
            }
            
            // Define available permissions
            $availablePermissions = [
                'view_store' => [
                    'label' => 'View Store',
                    'description' => 'Can view store details'
                ],
                'edit_store' => [
                    'label' => 'Edit Store',
                    'description' => 'Can edit store details'
                ],
                'view_products' => [
                    'label' => 'View Products',
                    'description' => 'Can view store products'
                ],
                'manage_products' => [
                    'label' => 'Manage Products',
                    'description' => 'Can add, edit, and update products'
                ],
                'delete_products' => [
                    'label' => 'Delete Products',
                    'description' => 'Can delete products'
                ],
                'view_orders' => [
                    'label' => 'View Orders',
                    'description' => 'Can view store orders'
                ],
                'manage_orders' => [
                    'label' => 'Manage Orders',
                    'description' => 'Can process and update orders'
                ],
                'delete_orders' => [
                    'label' => 'Delete Orders',
                    'description' => 'Can delete orders'
                ],
                'manage_staff' => [
                    'label' => 'Manage Staff',
                    'description' => 'Can manage store staff'
                ]
            ];
            
            // Get the current permissions of the staff member
            $staffPermissions = $staff->permissions ?? [];
            
            return view('store::admin.staff.edit', compact('store', 'staff', 'roles', 'availablePermissions', 'staffPermissions'));
        } catch (\Exception $e) {
            Log::error('Error in StoreStaffController@edit: ' . $e->getMessage());
            return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'An error occurred while loading the staff edit page. Please try again.');
        }
    }

    /**
     * Update the specified store staff member in storage.
     *
     * @param Request $request
     * @param int $storeId
     * @param int $staffId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $storeId, $staffId)
    {
        try {
            Log::info('Updating staff ID: ' . $staffId . ' for store ID: ' . $storeId);
            
            $request->validate([
                'role_id' => 'required|exists:roles,id',
                'permissions' => 'nullable|array',
            ]);
            
            // Check if store exists
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            // Check if staff exists
            $staff = $this->storeRepository->findStaffById($storeId, $staffId);
            
            if (!$staff) {
                Log::warning('Staff member not found with ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            $roleId = $request->input('role_id');
            Log::info('Updating staff role to ID: ' . $roleId);
            
            $result = $this->storeRepository->updateStoreStaff(
                $storeId,
                $staffId,
                $roleId
            );
            
            if (!$result) {
                Log::warning('Failed to update staff member ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Failed to update staff member.');
            }
            
            // Update permissions
            $permissions = $request->input('permissions', []);
            Log::info('Updating staff permissions: ' . json_encode($permissions));
            
            $permResult = $this->storeRepository->updateStaffPermissions($staffId, $permissions);
            
            if (!$permResult) {
                Log::warning('Failed to update permissions for staff ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('warning', 'Staff member updated but permissions could not be updated.');
            }
            
            return redirect()->route('admin.store.staff.index', $storeId)->with('success', 'Staff member updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating store staff: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update staff member. Please try again.');
        }
    }

    /**
     * Remove the specified store staff member from storage.
     *
     * @param int $storeId
     * @param int $staffId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($storeId, $staffId)
    {
        try {
            Log::info('Removing staff ID: ' . $staffId . ' from store ID: ' . $storeId);
            
            // Check if store exists
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            // Check if staff exists
            $staff = $this->storeRepository->findStaffById($storeId, $staffId);
            
            if (!$staff) {
                Log::warning('Staff member not found with ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            $result = $this->storeRepository->removeStoreStaff($storeId, $staffId);
            
            if (!$result) {
                Log::warning('Failed to remove staff member ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Failed to remove staff member.');
            }
            
            return redirect()->route('admin.store.staff.index', $storeId)->with('success', 'Staff member removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error removing store staff: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove staff member. Please try again.');
        }
    }

    /**
     * Show the form for managing staff permissions.
     *
     * @param int $storeId
     * @param int $staffId
     * @return \Illuminate\View\View
     */
    public function permissions($storeId, $staffId)
    {
        try {
            Log::info('Managing permissions for staff ID: ' . $staffId . ' in store ID: ' . $storeId);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            $staff = $this->storeRepository->findStaffById($storeId, $staffId);
            
            if (!$staff) {
                Log::warning('Staff member not found with ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            // Check if staff has a user relation
            if (!$staff->user) {
                Log::error('Staff user relation is null for staff ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Staff user information not found.');
            }
            
            $permissions = Permission::all()->groupBy(function ($permission) {
                return explode('.', $permission->name)[0];
            });
            
            $staffPermissions = $staff->user->getAllPermissions()->pluck('id')->toArray();
            
            return view('store::admin.staff.permissions', compact('store', 'staff', 'permissions', 'staffPermissions'));
        } catch (\Exception $e) {
            Log::error('Error in StoreStaffController@permissions: ' . $e->getMessage());
            return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'An error occurred while loading the permissions page. Please try again.');
        }
    }

    /**
     * Update the permissions for a staff member.
     *
     * @param Request $request
     * @param int $storeId
     * @param int $staffId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePermissions(Request $request, $storeId, $staffId)
    {
        try {
            Log::info('Updating permissions for staff ID: ' . $staffId . ' in store ID: ' . $storeId);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                Log::warning('Store not found with ID: ' . $storeId);
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }
            
            $staff = $this->storeRepository->findStaffById($storeId, $staffId);
            
            if (!$staff) {
                Log::warning('Staff member not found with ID: ' . $staffId);
                return redirect()->route('admin.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            $permissions = $request->input('permissions', []);
            Log::info('Updating permissions for staff: ' . json_encode($permissions));
            
            $result = $this->storeRepository->updateStaffPermissions($staffId, $permissions);
            
            if (!$result) {
                Log::warning('Failed to update permissions for staff ID: ' . $staffId);
                return redirect()->back()->with('error', 'Failed to update staff permissions. Please try again.');
            }
            
            return redirect()->route('admin.store.staff.index', $storeId)->with('success', 'Staff permissions updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating staff permissions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update staff permissions. Please try again.');
        }
    }
}
