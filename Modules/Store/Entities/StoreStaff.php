<?php

namespace Modules\Store\Entities;

use ArrayAccess;
use Illuminate\Support\Carbon;

class StoreStaff implements ArrayAccess
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var int|null
     */
    protected $roleId;

    /**
     * @var array|null
     */
    protected $userData;

    /**
     * @var array|null
     */
    protected $permissions;

    /**
     * @var Carbon
     */
    protected $createdAt;

    /**
     * @var Carbon
     */
    protected $updatedAt;

    /**
     * StoreStaff constructor.
     *
     * @param int $id
     * @param int $storeId
     * @param int $userId
     * @param int|null $roleId
     * @param array|null $userData
     * @param array|null $permissions
     * @param Carbon|null $createdAt
     * @param Carbon|null $updatedAt
     */
    public function __construct(
        int $id,
        int $storeId,
        int $userId,
        ?int $roleId = null,
        ?array $userData = null,
        ?array $permissions = null,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null
    ) {
        $this->id = $id;
        $this->storeId = $storeId;
        $this->userId = $userId;
        $this->roleId = $roleId;
        $this->userData = $userData;
        $this->permissions = $permissions;
        $this->createdAt = $createdAt ?? now();
        $this->updatedAt = $updatedAt ?? now();
    }

    /**
     * Create a new store staff entity from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? 0,
            $data['store_id'],
            $data['user_id'],
            $data['role_id'] ?? null,
            $data['user_data'] ?? null,
            $data['permissions'] ?? null,
            isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null
        );
    }

    /**
     * Convert the entity to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->storeId,
            'user_id' => $this->userId,
            'role_id' => $this->roleId,
            'user_data' => $this->userData,
            'permissions' => $this->permissions,
            'created_at' => $this->createdAt->toDateTimeString(),
            'updated_at' => $this->updatedAt->toDateTimeString(),
        ];
    }

    /**
     * Get the store staff ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the store ID.
     *
     * @return int
     */
    public function getStoreId(): int
    {
        return $this->storeId;
    }

    /**
     * Get the user ID.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * Get the role ID.
     *
     * @return int|null
     */
    public function getRoleId(): ?int
    {
        return $this->roleId;
    }

    /**
     * Get the user data.
     *
     * @return array|null
     */
    public function getUserData(): ?array
    {
        return $this->userData;
    }

    /**
     * Get the permissions.
     *
     * @return array|null
     */
    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    /**
     * Check if the staff has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }
        
        return in_array($permission, $this->permissions);
    }

    /**
     * Get the creation date.
     *
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    /**
     * Get the update date.
     *
     * @return Carbon
     */
    public function getUpdatedAt(): Carbon
    {
        return $this->updatedAt;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->$offset = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->$offset);
    }
}
