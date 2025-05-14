<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store as StoreModel;
use App\Models\StoreOwner as StoreOwnerModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Store\Entities\Store;
use Modules\Store\Entities\StoreOwner;
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;

class StoreOwnerController extends Controller
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * StoreOwnerController constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Display a listing of the store owners.
     *
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function index($storeId)
    {
        $store = $this->storeRepository->findById($storeId);
        
        if (!$store) {
            return redirect()->route('store.index')->with('error', 'Store not found.');
        }
        
        $owners = $this->storeRepository->getStoreOwners($storeId);
        
        return view('store::owners.index', compact('store', 'owners'));
    }

    /**
     * Show the form for adding a new store owner.
     *
     * @param int $storeId
     * @return \Illuminate\View\View
     */
    public function create($storeId)
    {
        $store = $this->storeRepository->findById($storeId);
        
        if (!$store) {
            return redirect()->route('store.index')->with('error', 'Store not found.');
        }
        
        $availableUsers = User::whereDoesntHave('storeOwners', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })->get();
        
        return view('store::owners.create', compact('store', 'availableUsers'));
    }

    /**
     * Store a newly created store owner in storage.
     *
     * @param Request $request
     * @param int $storeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $storeId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        
        try {
            $store = $this->storeRepository->findById($storeId);
            
            if (!$store) {
                return redirect()->route('admin.store.index')->with('error', 'Store not found');
            }
            
            $this->storeRepository->addStoreOwner($storeId, $request->user_id);
            
            return redirect()->route('store.owners.index', $storeId)->with('success', 'Store owner added successfully.');
        } catch (\Exception $e) {
            Log::error('Error adding store owner: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add store owner. Please try again.');
        }
    }

    /**
     * Remove the specified store owner from storage.
     *
     * @param int $storeId
     * @param int $ownerId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($storeId, $ownerId)
    {
        try {
            $result = $this->storeRepository->removeStoreOwner($storeId, $ownerId);
            
            if (!$result) {
                return redirect()->route('store.owners.index', $storeId)->with('error', 'Store owner not found.');
            }
            
            return redirect()->route('store.owners.index', $storeId)->with('success', 'Store owner removed successfully.');
        } catch (\Exception $e) {
            Log::error('Error removing store owner: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove store owner. Please try again.');
        }
    }
}
