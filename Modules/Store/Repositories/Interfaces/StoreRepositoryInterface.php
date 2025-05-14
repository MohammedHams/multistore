<?php

namespace Modules\Store\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Store\Entities\Store;
use Modules\Store\Entities\StoreOwner;
use Modules\Store\Entities\StoreStaff;

interface StoreRepositoryInterface
{
    /**
     * Get all stores.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(): \Illuminate\Support\Collection;

    /**
     * Get all stores with pagination and filtering.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllStores(int $perPage = 10, array $filters = []): LengthAwarePaginator;

    /**
     * Find store by ID.
     *
     * @param int $id
     * @return Store|null
     */
    public function findById(int $id): ?Store;

    /**
     * Create a new store.
     *
     * @param array $data
     * @return Store
     */
    public function createStore(array $data): Store;

    /**
     * Update an existing store.
     *
     * @param int $id
     * @param array $data
     * @return Store|null
     */
    public function updateStore(int $id, array $data): ?Store;

    /**
     * Delete a store.
     *
     * @param int $id
     * @return bool
     */
    public function deleteStore(int $id): bool;

    /**
     * Get store owners.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getStoreOwners(int $storeId): Collection;

    /**
     * Add store owner.
     *
     * @param int $storeId
     * @param int $userId
     * @return StoreOwner
     */
    public function addStoreOwner(int $storeId, int $userId): StoreOwner;

    /**
     * Remove store owner.
     *
     * @param int $storeId
     * @param int $ownerId
     * @return bool
     */
    public function removeStoreOwner(int $storeId, int $ownerId): bool;

    /**
     * Get store staff.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getStoreStaff(int $storeId): Collection;

    /**
     * Find staff by ID.
     *
     * @param int $storeId
     * @param int $staffId
     * @return StoreStaff|null
     */
    public function findStaffById(int $storeId, int $staffId): ?StoreStaff;

    /**
     * Add store staff.
     *
     * @param int $storeId
     * @param int $userId
     * @param int $roleId
     * @return StoreStaff
     */
    public function addStoreStaff(int $storeId, int $userId, int $roleId): StoreStaff;

    /**
     * Update store staff.
     *
     * @param int $storeId
     * @param int $staffId
     * @param int $roleId
     * @return StoreStaff|null
     */
    public function updateStoreStaff(int $storeId, int $staffId, int $roleId): ?StoreStaff;

    /**
     * Remove store staff.
     *
     * @param int $storeId
     * @param int $staffId
     * @return bool
     */
    public function removeStoreStaff(int $storeId, int $staffId): bool;

    /**
     * Update staff permissions.
     *
     * @param int $staffId
     * @param array $permissions
     * @return bool
     */
    public function updateStaffPermissions(int $staffId, array $permissions): bool;
}
