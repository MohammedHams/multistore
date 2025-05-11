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
use Modules\Store\app\Repositories\Interfaces\StoreRepositoryInterface;

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
     * @param StoreModel $store
     * @return \Illuminate\View\View
     */
    public function index(StoreModel $store)
    {
        try {
            $owners = $this->storeRepository->getStoreOwners($store->id);
            
            return view('store::owners.index', compact('store', 'owners'));
        } catch (\Exception $e) {
            Log::error('Error fetching store owners: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while fetching store owners. Please try again.'));
        }
    }

    /**
     * Show the form for adding a new owner to the store.
     *
     * @param StoreModel $store
     * @return \Illuminate\View\View
     */
    public function create(StoreModel $store)
    {
        try {
            // Get users who are not already owners of this store
            $currentOwnerIds = StoreOwnerModel::where('store_id', $store->id)->pluck('user_id')->toArray();
            $availableUsers = User::whereNotIn('id', $currentOwnerIds)->get();
            
            return view('store::owners.create', compact('store', 'availableUsers'));
        } catch (\Exception $e) {
            Log::error('Error loading owner creation form: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while loading the form. Please try again.'));
        }
    }

    /**
     * Store a newly created owner in the store.
     *
     * @param Request $request
     * @param StoreModel $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, StoreModel $store)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id']
        ]);
        
        try {
            $this->storeRepository->addOwner($store->id, $request->input('user_id'));
            
            return redirect()->route('store.owners.index', $store)
                ->with('success', __('Owner added to store successfully.'));
        } catch (\Exception $e) {
            Log::error('Error adding owner to store: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while adding the owner. Please try again.'))
                ->withInput();
        }
    }

    /**
     * Remove the specified owner from the store.
     *
     * @param StoreModel $store
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(StoreModel $store, User $user)
    {
        try {
            $result = $this->storeRepository->removeOwner($store->id, $user->id);
            
            if (!$result) {
                return back()->with('error', __('store.cannot_remove_last_owner'));
            }
            
            return redirect()->route('store.owners.index', $store)
                ->with('success', __('store.owner_removed'));
        } catch (\Exception $e) {
            Log::error('Error removing owner from store: ' . $e->getMessage());
            return back()->with('error', __('dashboard.error_occurred'));
        }
    }
}
