<?php

namespace Modules\Store\app\Repositories\Interfaces;

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
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllWithPagination(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    
    /**
     * Get all active stores.
     *
     * @return array
     */
    public function getAllActive(): array;
    
    /**
     * Find store by ID.
     *
     * @param int $id
     * @return Store|null
     */
    public function findById(int $id): ?Store;
    
    /**
     * Find store by domain.
     *
     * @param string $domain
     * @return Store|null
     */
    public function findByDomain(string $domain): ?Store;
    
    /**
     * Create a new store.
     *
     * @param array $data
     * @param User $owner
     * @return Store
     */
    public function create(array $data, User $owner): Store;
    
    /**
     * Update an existing store.
     *
     * @param int $id
     * @param array $data
     * @return Store|null
     */
    public function update(int $id, array $data): ?Store;
    
    /**
     * Delete a store.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
    
    /**
     * Add an owner to a store.
     *
     * @param int $storeId
     * @param int $userId
     * @return bool
     */
    public function addOwner(int $storeId, int $userId): bool;
    
    /**
     * Remove an owner from a store.
     *
     * @param int $storeId
     * @param int $userId
     * @return bool
     */
    public function removeOwner(int $storeId, int $userId): bool;
    
    /**
     * Add a staff member to a store.
     *
     * @param int $storeId
     * @param int $userId
     * @param array $permissions
     * @return bool
     */
    public function addStaff(int $storeId, int $userId, array $permissions = []): bool;
    
    /**
     * Update staff permissions.
     *
     * @param int $storeId
     * @param int $userId
     * @param array $permissions
     * @return bool
     */
    public function updateStaffPermissions(int $storeId, int $userId, array $permissions): bool;
    
    /**
     * Remove a staff member from a store.
     *
     * @param int $storeId
     * @param int $userId
     * @return bool
     */
    public function removeStaff(int $storeId, int $userId): bool;
    
    /**
     * Get all owners for a store.
     *
     * @param int $storeId
     * @return array
     */
    public function getStoreOwners(int $storeId): array;
    
    /**
     * Get all staff for a store.
     *
     * @param int $storeId
     * @return array
     */
    public function getStoreStaff(int $storeId): array;
}
