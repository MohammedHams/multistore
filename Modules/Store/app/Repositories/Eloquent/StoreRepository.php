<?php

namespace Modules\Store\app\Repositories\Eloquent;

use App\Models\Store as StoreModel;
use App\Models\StoreOwner as StoreOwnerModel;
use App\Models\StoreStaff as StoreStaffModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Store\Entities\Store;
use Modules\Store\Entities\StoreOwner;
use Modules\Store\Entities\StoreStaff;
use Modules\Store\app\Repositories\Interfaces\StoreRepositoryInterface;

class StoreRepository implements StoreRepositoryInterface
{
    /**
     * @var StoreModel
     */
    protected $model;

    /**
     * StoreRepository constructor.
     *
     * @param StoreModel $model
     */
    public function __construct(StoreModel $model)
    {
        $this->model = $model;
    }
    
    /**
     * Get all stores.
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(): \Illuminate\Support\Collection
    {
        try {
            $stores = $this->model->all();
            return $this->transformCollection($stores);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error retrieving all stores: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get all stores with pagination and filtering.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllWithPagination(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['domain'])) {
            $query->where('domain', 'like', '%' . $filters['domain'] . '%');
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        // Apply sorting
        $sortField = $filters['sort_field'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $paginator = $query->paginate($perPage);
        
        // Transform the paginator items to Store entities
        $paginator->getCollection()->transform(function ($storeModel) {
            return $this->mapModelToEntity($storeModel);
        });
        
        return $paginator;
    }

    /**
     * Get all active stores.
     *
     * @return array
     */
    public function getAllActive(): array
    {
        $storeModels = $this->model->active()->get();
        $stores = [];
        
        foreach ($storeModels as $storeModel) {
            $stores[] = $this->mapModelToEntity($storeModel);
        }
        
        return $stores;
    }

    /**
     * Find store by ID.
     *
     * @param int $id
     * @return Store|null
     */
    public function findById(int $id): ?Store
    {
        $storeModel = $this->model->find($id);
        
        if (!$storeModel) {
            return null;
        }
        
        return $this->mapModelToEntity($storeModel);
    }

    /**
     * Find store by domain.
     *
     * @param string $domain
     * @return Store|null
     */
    public function findByDomain(string $domain): ?Store
    {
        $storeModel = $this->model->where('domain', $domain)->first();
        
        if (!$storeModel) {
            return null;
        }
        
        return $this->mapModelToEntity($storeModel);
    }

    /**
     * Create a new store.
     *
     * @param array $data
     * @param User $owner
     * @return Store
     */
    public function create(array $data, User $owner): Store
    {
        $storeModel = $this->model->create($data);
        
        // Add the user as the store owner
        $this->addOwner($storeModel->id, $owner->id);
        
        return $this->mapModelToEntity($storeModel);
    }

    /**
     * Update an existing store.
     *
     * @param int $id
     * @param array $data
     * @return Store|null
     */
    public function update(int $id, array $data): ?Store
    {
        $store = $this->findById($id);
        
        if (!$store) {
            return null;
        }
        
        // Update the model in the database
        $storeModel = $this->model->find($id);
        $storeModel->update($data);
        
        // Update and return the entity
        return $store->update($data);
    }

    /**
     * Delete a store.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $storeModel = $this->model->find($id);
        
        if (!$storeModel) {
            return false;
        }
        
        return $storeModel->delete();
    }

    /**
     * Add an owner to a store.
     *
     * @param int $storeId
     * @param int $userId
     * @return bool
     */
    public function addOwner(int $storeId, int $userId): bool
    {
        $storeModel = $this->model->find($storeId);
        
        if (!$storeModel) {
            return false;
        }
        
        // Check if the user is already an owner of this store
        $existingOwner = StoreOwnerModel::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->first();
            
        if ($existingOwner) {
            return true; // User is already an owner
        }
        
        // Create a new store owner record
        StoreOwnerModel::create([
            'store_id' => $storeId,
            'user_id' => $userId
        ]);
        
        return true;
    }

    /**
     * Add a staff member to a store.
     *
     * @param int $storeId
     * @param int $userId
     * @param array $permissions
     * @return bool
     */
    public function addStaff(int $storeId, int $userId, array $permissions = []): bool
    {
        $storeModel = $this->model->find($storeId);
        
        if (!$storeModel) {
            return false;
        }
        
        // Check if the user is already a staff member of this store
        $existingStaff = StoreStaffModel::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->first();
            
        if ($existingStaff) {
            // Update permissions if the staff member already exists
            return $this->updateStaffPermissions($storeId, $userId, $permissions);
        }
        
        // Create a new store staff record
        StoreStaffModel::create([
            'store_id' => $storeId,
            'user_id' => $userId,
            'permissions' => $permissions
        ]);
        
        return true;
    }

    /**
     * Update staff permissions.
     *
     * @param int $storeId
     * @param int $userId
     * @param array $permissions
     * @return bool
     */
    public function updateStaffPermissions(int $storeId, int $userId, array $permissions): bool
    {
        $staffModel = StoreStaffModel::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->first();
        
        if (!$staffModel) {
            return false;
        }
        
        $staffModel->update(['permissions' => $permissions]);
        return true;
    }

    /**
     * Remove a staff member from a store.
     *
     * @param int $storeId
     * @param int $userId
     * @return bool
     */
    public function removeStaff(int $storeId, int $userId): bool
    {
        return StoreStaffModel::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Get all owners for a store.
     *
     * @param int $storeId
     * @return array
     */
    public function getStoreOwners(int $storeId): array
    {
        $storeModel = $this->model->find($storeId);
        
        if (!$storeModel) {
            return [];
        }
        
        $ownerIds = StoreOwnerModel::where('store_id', $storeId)->pluck('user_id');
        $userModels = User::whereIn('id', $ownerIds)->get();
        
        $owners = [];
        foreach ($userModels as $userModel) {
            $storeOwnerModel = StoreOwnerModel::where('store_id', $storeId)
                ->where('user_id', $userModel->id)
                ->first();
            
            $owner = StoreOwner::fromArray([
                'id' => $storeOwnerModel->id,
                'store_id' => $storeId,
                'user_id' => $userModel->id,
                'created_at' => $storeOwnerModel->created_at,
                'updated_at' => $storeOwnerModel->updated_at,
                'user_data' => [
                    'id' => $userModel->id,
                    'name' => $userModel->name,
                    'email' => $userModel->email
                ]
            ]);
            
            $owners[] = $owner;
        }
        
        return $owners;
    }
    
    /**
     * Remove an owner from a store.
     *
     * @param int $storeId
     * @param int $userId
     * @return bool
     */
    public function removeOwner(int $storeId, int $userId): bool
    {
        // Check if this is the last owner
        $ownerCount = StoreOwnerModel::where('store_id', $storeId)->count();
        
        if ($ownerCount <= 1) {
            return false; // Cannot remove the last owner
        }
        
        return StoreOwnerModel::where('store_id', $storeId)
            ->where('user_id', $userId)
            ->delete();
    }
    
    /**
     * Transform a model to an entity.
     *
     * @param StoreModel $model
     * @return Store
     */
    protected function transformToEntity(StoreModel $model): Store
    {
        return Store::fromArray($model->toArray());
    }
    
    /**
     * Transform a collection of models to a collection of entities.
     *
     * @param Collection $models
     * @return \Illuminate\Support\Collection
     */
    protected function transformCollection(Collection $models): \Illuminate\Support\Collection
    {
        return $models->map(function ($model) {
            return $this->transformToEntity($model);
        });
    }

    /**
     * Get all staff for a store.
     *
     * @param int $storeId
     * @return array
     */
    public function getStoreStaff(int $storeId): array
    {
        $storeModel = $this->model->find($storeId);
        
        if (!$storeModel) {
            return [];
        }
        
        $staffIds = StoreStaffModel::where('store_id', $storeId)->pluck('user_id');
        $userModels = User::whereIn('id', $staffIds)->get();
        
        $staffMembers = [];
        foreach ($userModels as $userModel) {
            $staffModel = StoreStaffModel::where('store_id', $storeId)
                ->where('user_id', $userModel->id)
                ->first();
            
            $staff = StoreStaff::fromArray([
                'id' => $staffModel->id,
                'store_id' => $storeId,
                'user_id' => $userModel->id,
                'permissions' => $staffModel->permissions,
                'created_at' => $staffModel->created_at,
                'updated_at' => $staffModel->updated_at,
                'user_data' => [
                    'id' => $userModel->id,
                    'name' => $userModel->name,
                    'email' => $userModel->email
                ]
            ]);
            
            $staffMembers[] = $staff;
        }
        
        return $staffMembers;
    }
    
    /**
     * Map a store model to a store entity.
     *
     * @param StoreModel $model
     * @return Store
     */
    private function mapModelToEntity(StoreModel $model): Store
    {
        return Store::fromArray([
            'id' => $model->id,
            'name' => $model->name,
            'domain' => $model->domain,
            'email' => $model->email,
            'phone' => $model->phone,
            'logo' => $model->logo,
            'is_active' => $model->is_active,
            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ]);
    }
}
