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
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $stores = $this->storeRepository->getAllStores();
        return view('store::index', compact('stores'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('store::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreStoreRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle logo upload if present
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoPath = $logo->store('store_logos', 'public');
                $data['logo'] = $logoPath;
            }

            $store = $this->storeRepository->createStore($data);

            return redirect()->route('admin.store.index')->with('success', 'Store created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create store. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $storeModel = $this->storeRepository->findById($id);

        if (!$storeModel) {
            return redirect()->route('admin.store.index')->with('error', 'Store not found.');
        }

        // Fetch store owners for this store
        $owners = $this->storeRepository->getStoreOwners($id);

        // Fetch store staff for this store (in case it's used in the view as well)
        $staff = $this->storeRepository->getStoreStaff($id);

        return view('store::show', compact('storeModel', 'owners', 'staff'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $store = $this->storeRepository->findById($id);

        if (!$store) {
            return redirect()->route('admin.store.index')->with('error', 'Store not found.');
        }

        return view('store::edit', compact('store'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateStoreRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateStoreRequest $request, $id)
    {
        try {
            $data = $request->validated();

            // Handle logo upload if present
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoPath = $logo->store('store_logos', 'public');
                $data['logo'] = $logoPath;

                // Delete old logo if exists
                $store = $this->storeRepository->findById($id);
                if ($store && $store->logo) {
                    Storage::disk('public')->delete($store->logo);
                }
            }

            $store = $this->storeRepository->updateStore($id, $data);

            if (!$store) {
                return redirect()->route('admin.store.index')->with('error', 'Store not found.');
            }

            return redirect()->route('admin.store.index')->with('success', 'Store updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update store. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $result = $this->storeRepository->deleteStore($id);

            if (!$result) {
                return redirect()->route('admin.store.index')->with('error', 'Store not found');
            }

            return redirect()->route('admin.store.index')->with('success', 'Store deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting store: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete store. Please try again.');
        }
    }
}
