<?php

namespace Modules\Store\Entities;

use ArrayAccess;
use Illuminate\Support\Carbon;

class StoreOwner implements ArrayAccess
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
     * @var Carbon
     */
    protected $createdAt;

    /**
     * @var Carbon
     */
    protected $updatedAt;

    /**
     * @var array|null
     */
    protected $userData;

    /**
     * StoreOwner constructor.
     *
     * @param int $id
     * @param int $storeId
     * @param int $userId
     * @param Carbon|null $createdAt
     * @param Carbon|null $updatedAt
     * @param array|null $userData
     */
    public function __construct(
        int $id,
        int $storeId,
        int $userId,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null,
        ?array $userData = null
    ) {
        $this->id = $id;
        $this->storeId = $storeId;
        $this->userId = $userId;
        $this->createdAt = $createdAt ?? now();
        $this->updatedAt = $updatedAt ?? now();
        $this->userData = $userData;
    }

    /**
     * Create a new store owner entity from an array.
     *
     * @param array $data
     * @param int|null $id
     * @return self
     */
    public static function fromArray(array $data, ?int $id = null): self
    {
        return new self(
            $id ?? ($data['id'] ?? 0),
            $data['store_id'],
            $data['user_id'],
            isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            $data['user_data'] ?? null
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
            'created_at' => $this->createdAt->toDateTimeString(),
            'updated_at' => $this->updatedAt->toDateTimeString(),
            'user_data' => $this->userData,
        ];
    }

    /**
     * Get the store owner ID.
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
     * Get the created at timestamp.
     *
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->createdAt;
    }

    /**
     * Get the updated at timestamp.
     *
     * @return Carbon
     */
    public function getUpdatedAt(): Carbon
    {
        return $this->updatedAt;
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
     * Set the user data.
     *
     * @param array|null $userData
     * @return self
     */
    public function setUserData(?array $userData): self
    {
        $this->userData = $userData;
        return $this;
    }
    
    /**
     * Get the name attribute from user data.
     *
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        return $this->userData['name'] ?? null;
    }
    
    /**
     * Get the email attribute from user data.
     *
     * @return string|null
     */
    public function getEmailAttribute(): ?string
    {
        return $this->userData['email'] ?? null;
    }
    
    /**
     * Magic getter for properties.
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($name === 'name') {
            return $this->getNameAttribute();
        }
        
        if ($name === 'email') {
            return $this->getEmailAttribute();
        }
        
        if (method_exists($this, 'get' . ucfirst($name) . 'Attribute')) {
            $method = 'get' . ucfirst($name) . 'Attribute';
            return $this->$method();
        }
        
        if (isset($this->userData) && is_array($this->userData) && isset($this->userData[$name])) {
            return $this->userData[$name];
        }
        
        return null;
    }
    
    /**
     * Determine if an offset exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->$offset) || 
               method_exists($this, 'get' . ucfirst($offset) . 'Attribute') ||
               (isset($this->userData) && is_array($this->userData) && isset($this->userData[$offset]));
    }
    
    /**
     * Get the value at the given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        // Check if the property exists directly
        if (isset($this->$offset)) {
            return $this->$offset;
        }
        
        // Check if there's a getter method for this property
        $method = 'get' . ucfirst($offset) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        
        // Check if it's in the userData array
        if (isset($this->userData) && is_array($this->userData) && isset($this->userData[$offset])) {
            return $this->userData[$offset];
        }
        
        return null;
    }
    
    /**
     * Set the value at the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        // Entities should be immutable from outside, so we don't implement this
    }
    
    /**
     * Unset the value at the given offset.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        // Entities should be immutable from outside, so we don't implement this
    }
}
