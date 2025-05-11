<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Store as StoreModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Entities\Store;
use Modules\Store\Http\Requests\StoreStoreRequest;
use Modules\Store\Http\Requests\UpdateStoreRequest;
use Modules\Store\app\Repositories\Interfaces\StoreRepositoryInterface;

class StoreController extends Controller
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * StoreController constructor.
     *
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(StoreRepositoryInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }

    /**
     * Display a listing of the stores.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['name', 'domain', 'email', 'is_active', 'sort_field', 'sort_direction']);
            $stores = $this->storeRepository->getAllWithPagination($filters);
            
            return view('store::index', compact('stores', 'filters'));
        } catch (\Exception $e) {
            Log::error('Error fetching stores: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while fetching stores. Please try again.'));
        }
    }

    /**
     * Show the form for creating a new store.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('store::create');
    }

    /**
     * Store a newly created store in storage.
     *
     * @param StoreStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreStoreRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('store-logos', 'public');
                $data['logo'] = $path;
            }
            
            // Set default values
            $data['is_active'] = $data['is_active'] ?? true;
            $data['settings'] = $data['settings'] ?? [];
            
            // Create the store and add the current user as the owner
            $store = $this->storeRepository->create($data, auth()->user());
            
            return redirect()->route('store.show', $store)
                ->with('success', __('Store created successfully.'));
        } catch (\Exception $e) {
            Log::error('Error creating store: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while creating the store. Please try again.'))
                ->withInput();
        }
    }

    /**
     * Display the specified store.
     *
     * @param StoreModel $store
     * @return \Illuminate\View\View
     */
    public function show(StoreModel $store)
    {
        try {
            $owners = $this->storeRepository->getStoreOwners($store->id);
            $staff = $this->storeRepository->getStoreStaff($store->id);
            
            return view('store::show', compact('store', 'owners', 'staff'));
        } catch (\Exception $e) {
            Log::error('Error showing store: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while fetching store details. Please try again.'));
        }
    }

    /**
     * Show the form for editing the specified store.
     *
     * @param StoreModel $store
     * @return \Illuminate\View\View
     */
    public function edit(StoreModel $store)
    {
        return view('store::edit', compact('store'));
    }

    /**
     * Update the specified store in storage.
     *
     * @param UpdateStoreRequest $request
     * @param StoreModel $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateStoreRequest $request, StoreModel $store)
    {
        try {
            $data = $request->validated();
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($store->logo) {
                    Storage::disk('public')->delete($store->logo);
                }
                
                $path = $request->file('logo')->store('store-logos', 'public');
                $data['logo'] = $path;
            }
            
            $this->storeRepository->update($store->id, $data);
            
            return redirect()->route('store.show', $store)
                ->with('success', __('Store updated successfully.'));
        } catch (\Exception $e) {
            Log::error('Error updating store: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while updating the store. Please try again.'))
                ->withInput();
        }
    }

    /**
     * Remove the specified store from storage.
     *
     * @param StoreModel $store
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(StoreModel $store)
    {
        try {
            // Delete logo if exists
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            
            $this->storeRepository->delete($store->id);
            
            return redirect()->route('store.index')
                ->with('success', __('Store deleted successfully.'));
        } catch (\Exception $e) {
            Log::error('Error deleting store: ' . $e->getMessage());
            return back()->with('error', __('An error occurred while deleting the store. Please try again.'));
        }
    }
}
