<?php

namespace Modules\Store\Repositories\Eloquent;

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
use Modules\Store\Repositories\Interfaces\StoreRepositoryInterface;

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
        return $this->model->all();
    }

    /**
     * Get all stores with pagination and filtering.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllStores(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        // Apply filters if provided
        if (!empty($filters)) {
            if (isset($filters['name'])) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            }

            if (isset($filters['status'])) {
                $query->where('status', $filters['status']);
            }
        }

        return $query->paginate($perPage);
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
     * Create a new store.
     *
     * @param array $data
     * @return Store
     */
    public function createStore(array $data): Store
    {
        $storeModel = $this->model->create($data);
        return $this->mapModelToEntity($storeModel);
    }

    /**
     * Update an existing store.
     *
     * @param int $id
     * @param array $data
     * @return Store|null
     */
    public function updateStore(int $id, array $data): ?Store
    {
        $storeModel = $this->model->find($id);

        if (!$storeModel) {
            return null;
        }

        $storeModel->update($data);
        return $this->mapModelToEntity($storeModel->fresh());
    }

    /**
     * Delete a store.
     *
     * @param int $id
     * @return bool
     */
    public function deleteStore(int $id): bool
    {
        $storeModel = $this->model->find($id);

        if (!$storeModel) {
            return false;
        }

        return $storeModel->delete();
    }

    /**
     * Get store owners.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getStoreOwners(int $storeId): Collection
    {
        $storeOwnerModels = StoreOwnerModel::where('store_id', $storeId)
            ->with('user')
            ->get();
            
        // Map the Eloquent models to entity objects
        $mappedOwners = $storeOwnerModels->map(function ($model) {
            return $this->mapOwnerModelToEntity($model);
        });
        
        // Convert Illuminate\Support\Collection to Illuminate\Database\Eloquent\Collection
        return new Collection($mappedOwners->all());
    }

    /**
     * Add store owner.
     *
     * @param int $storeId
     * @param int $userId
     * @return StoreOwner
     */
    public function addStoreOwner(int $storeId, int $userId): StoreOwner
    {
        $storeOwnerModel = StoreOwnerModel::create([
            'store_id' => $storeId,
            'user_id' => $userId,
        ]);

        return $this->mapOwnerModelToEntity($storeOwnerModel);
    }

    /**
     * Remove store owner.
     *
     * @param int $storeId
     * @param int $ownerId
     * @return bool
     */
    public function removeStoreOwner(int $storeId, int $ownerId): bool
    {
        $storeOwner = StoreOwnerModel::where('store_id', $storeId)
            ->where('id', $ownerId)
            ->first();

        if (!$storeOwner) {
            return false;
        }

        return $storeOwner->delete();
    }

    /**
     * Get store staff.
     *
     * @param int $storeId
     * @return Collection
     */
    public function getStoreStaff(int $storeId): Collection
    {
        $staffModels = StoreStaffModel::where('store_id', $storeId)
            ->with(['user']) // Remove the 'role' relationship since it doesn't exist
            ->get();
            
        // Map the Eloquent models to entity objects or return as is if no entity mapping is needed
        $mappedStaff = $staffModels->map(function ($model) {
            return $this->mapStaffModelToEntity($model);
        });
        
        // Convert Illuminate\Support\Collection to Illuminate\Database\Eloquent\Collection
        return new Collection($mappedStaff->all());
    }

    /**
     * Find staff by ID.
     *
     * @param int $storeId
     * @param int $staffId
     * @return StoreStaff|null
     */
    public function findStaffById(int $storeId, int $staffId): ?StoreStaff
    {
        $staffModel = StoreStaffModel::where('store_id', $storeId)
            ->where('id', $staffId)
            ->with(['user']) // Remove the 'role' relationship since it doesn't exist
            ->first();

        if (!$staffModel) {
            return null;
        }

        return $this->mapStaffModelToEntity($staffModel);
    }

    /**
     * Add store staff.
     *
     * @param int $storeId
     * @param int $userId
     * @param int $roleId
     * @param array $permissions
     * @return StoreStaff
     */
    public function addStoreStaff(int $storeId, int $userId, int $roleId, array $permissions = []): StoreStaff
    {
        $staffModel = StoreStaffModel::create([
            'store_id' => $storeId,
            'user_id' => $userId,
            'role_id' => $roleId,
            'permissions' => !empty($permissions) ? $permissions : null,
        ]);

        // Sync the role permissions to the user
        $user = User::find($userId);
        $role = DB::table('roles')->where('id', $roleId)->first();

        if ($user && $role) {
            $user->assignRole($role->name);
            
            // If permissions are provided, sync them to the user
            if (!empty($permissions)) {
                $user->syncPermissions($permissions);
            }
        }

        return $this->mapStaffModelToEntity($staffModel);
    }

    /**
     * Update store staff.
     *
     * @param int $storeId
     * @param int $staffId
     * @param int $roleId
     * @return StoreStaff|null
     */
    public function updateStoreStaff(int $storeId, int $staffId, int $roleId): ?StoreStaff
    {
        $staffModel = StoreStaffModel::where('store_id', $storeId)
            ->where('id', $staffId)
            ->first();

        if (!$staffModel) {
            return null;
        }

        // Remove old role
        $user = User::find($staffModel->user_id);
        $oldRole = DB::table('roles')->where('id', $staffModel->role_id)->first();

        if ($user && $oldRole) {
            $user->removeRole($oldRole->name);
        }

        // Update staff record
        $staffModel->update([
            'role_id' => $roleId,
        ]);

        // Assign new role
        $newRole = DB::table('roles')->where('id', $roleId)->first();

        if ($user && $newRole) {
            $user->assignRole($newRole->name);
        }

        return $this->mapStaffModelToEntity($staffModel->fresh());
    }

    /**
     * Remove store staff.
     *
     * @param int $storeId
     * @param int $staffId
     * @return bool
     */
    public function removeStoreStaff(int $storeId, int $staffId): bool
    {
        $staffModel = StoreStaffModel::where('store_id', $storeId)
            ->where('id', $staffId)
            ->first();

        if (!$staffModel) {
            return false;
        }

        // Remove role
        $user = User::find($staffModel->user_id);
        $role = DB::table('roles')->where('id', $staffModel->role_id)->first();

        if ($user && $role) {
            $user->removeRole($role->name);
        }

        return $staffModel->delete();
    }

    /**
     * Update staff permissions.
     *
     * @param int $staffId
     * @param array $permissions
     * @return bool
     */
    public function updateStaffPermissions(int $staffId, array $permissions): bool
    {
        $staffModel = StoreStaffModel::find($staffId);

        if (!$staffModel) {
            return false;
        }

        // Update permissions directly in the StoreStaff model
        $staffModel->update([
            'permissions' => $permissions
        ]);

        return true;
    }

    /**
     * Map a store model to a store entity.
     *
     * @param StoreModel $model
     * @return Store
     */
    protected function mapModelToEntity(StoreModel $model): Store
    {
        // Create a Store entity with required constructor parameters
        // Only use the constructor parameters that are available in the Store entity class
        $store = new Store(
            $model->id,
            $model->name,
            $model->domain ?? '',  // Provide a default value if domain is null
            $model->email ?? '',   // Provide a default value if email is null
            $model->phone ?? '',   // Provide a default value if phone is null
            $model->logo,
            $model->status == 'active',
            $model->created_at,
            $model->updated_at
        );

        return $store;
    }
    
    /**
     * Map a store owner model to a store owner entity.
     *
     * @param StoreOwnerModel $model
     * @return StoreOwner
     */
    protected function mapOwnerModelToEntity(StoreOwnerModel $model): StoreOwner
    {
        // Extract user data if the user relation is loaded
        $userData = null;
        if ($model->relationLoaded('user') && $model->user) {
            $userData = [
                'id' => $model->user->id,
                'name' => $model->user->name,
                'email' => $model->user->email,
                'role' => $model->user->role ?? 'user',
            ];
        }
        
        // Create a StoreOwner entity with required constructor parameters
        return new StoreOwner(
            $model->id,
            $model->store_id,
            $model->user_id,
            $userData,
            $model->created_at,
            $model->updated_at
        );
    }

    /**
     * Map a store staff model to a store staff entity.
     *
     * @param StoreStaffModel $model
     * @return StoreStaff
     */
    protected function mapStaffModelToEntity(StoreStaffModel $model): StoreStaff
    {
        // Extract user data if the user relation is loaded
        $userData = null;
        if ($model->relationLoaded('user') && $model->user) {
            $userData = [
                'id' => $model->user->id,
                'name' => $model->user->name,
                'email' => $model->user->email,
                'role' => $model->user->role ?? 'staff',
            ];
        }
        
        // Ensure permissions is an array
        $permissions = $model->permissions;
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true) ?? [];
        } elseif ($permissions === null) {
            $permissions = [];
        }
        
        // Create a StoreStaff entity with required constructor parameters
        return new StoreStaff(
            $model->id,
            $model->store_id,
            $model->user_id,
            $model->role_id,
            $userData,
            $permissions,
            $model->created_at,
            $model->updated_at
        );
    }
}
