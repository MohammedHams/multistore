<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;

class StoreOwnerStaffController extends Controller
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * StoreOwnerStaffController constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
        $this->middleware('auth:store-owner');
    }

    /**
     * Display a listing of the store staff.
     *
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function index($storeId)
    {
        $store = $this->storeRepository->findById($storeId);
        
        if (!$store) {
            return redirect()->route('store-owner.dashboard')->with('error', 'Store not found.');
        }
        
        // Check if the authenticated store owner owns this store
        $storeOwner = auth('store-owner')->user();
        if ($storeOwner->store_id != $storeId) {
            return redirect()->route('store-owner.dashboard')->with('error', 'You do not have permission to manage this store.');
        }
        
        $staff = $this->storeRepository->getStoreStaff($storeId);
        
        return view('store::store-owner.staff.index', compact('store', 'staff'));
    }

    /**
     * Show the form for adding a new store staff member.
     *
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function create($storeId)
    {
        $store = $this->storeRepository->findById($storeId);
        
        if (!$store) {
            return redirect()->route('store-owner.dashboard')->with('error', 'Store not found.');
        }
        
        // Check if the authenticated store owner owns this store
        $storeOwner = auth('store-owner')->user();
        if ($storeOwner->store_id != $storeId) {
            return redirect()->route('store-owner.dashboard')->with('error', 'You do not have permission to manage this store.');
        }
        
        $availableUsers = User::whereDoesntHave('storeStaff', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })->get();
        
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
        
        return view('store::store-owner.staff.create', compact('store', 'availableUsers', 'availablePermissions'));
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
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string',
            ]);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                return redirect()->route('store-owner.dashboard')->with('error', 'Store not found.');
            }
            
            // Check if the authenticated store owner owns this store
            $storeOwner = auth('store-owner')->user();
            if ($storeOwner->store_id != $storeId) {
                return redirect()->route('store-owner.dashboard')->with('error', 'You do not have permission to manage this store.');
            }
            
            $permissions = $request->input('permissions', []);
            $userId = $request->input('user_id');
            
            $this->storeRepository->addStoreStaff($storeId, $userId, $permissions);
            
            return redirect()->route('store-owner.store.staff.index', $storeId)->with('success', 'Staff member added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding store staff: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add staff member. Please try again.');
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
        $store = $this->storeRepository->findById($storeId);
        
        if (!$store) {
            return redirect()->route('store-owner.dashboard')->with('error', 'Store not found.');
        }
        
        // Check if the authenticated store owner owns this store
        $storeOwner = auth('store-owner')->user();
        if ($storeOwner->store_id != $storeId) {
            return redirect()->route('store-owner.dashboard')->with('error', 'You do not have permission to manage this store.');
        }
        
        $staff = $this->storeRepository->findStaffById($storeId, $staffId);
        
        if (!$staff) {
            return redirect()->route('store-owner.store.staff.index', $storeId)->with('error', 'Staff member not found.');
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
        
        // Get current permissions
        $currentPermissions = $staff->getPermissions() ?? [];
        
        return view('store::store-owner.staff.edit', compact('store', 'staff', 'availablePermissions', 'currentPermissions'));
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
            $request->validate([
                'permissions' => 'nullable|array',
                'permissions.*' => 'string',
            ]);
            
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                return redirect()->route('store-owner.dashboard')->with('error', 'Store not found.');
            }
            
            // Check if the authenticated store owner owns this store
            $storeOwner = auth('store-owner')->user();
            if ($storeOwner->store_id != $storeId) {
                return redirect()->route('store-owner.dashboard')->with('error', 'You do not have permission to manage this store.');
            }
            
            $staff = $this->storeRepository->findStaffById($storeId, $staffId);
            
            if (!$staff) {
                return redirect()->route('store-owner.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            $permissions = $request->input('permissions', []);
            
            $this->storeRepository->updateStaffPermissions($staffId, $permissions);
            
            return redirect()->route('store-owner.store.staff.index', $storeId)->with('success', 'Staff permissions updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating staff permissions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update staff permissions. Please try again.');
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
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                return redirect()->route('store-owner.dashboard')->with('error', 'Store not found.');
            }
            
            // Check if the authenticated store owner owns this store
            $storeOwner = auth('store-owner')->user();
            if ($storeOwner->store_id != $storeId) {
                return redirect()->route('store-owner.dashboard')->with('error', 'You do not have permission to manage this store.');
            }
            
            $result = $this->storeRepository->removeStoreStaff($storeId, $staffId);
            
            if (!$result) {
                return redirect()->route('store-owner.store.staff.index', $storeId)->with('error', 'Staff member not found.');
            }
            
            return redirect()->route('store-owner.store.staff.index', $storeId)->with('success', 'Staff member removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error removing store staff: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove staff member. Please try again.');
        }
    }
}
