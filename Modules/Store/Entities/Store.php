<?php

namespace Modules\Store\Entities;

use ArrayAccess;
use Illuminate\Support\Carbon;

class Store implements ArrayAccess
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var string|null
     */
    protected $logo;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @var Carbon
     */
    protected $createdAt;

    /**
     * @var Carbon
     */
    protected $updatedAt;

    /**
     * Store constructor.
     *
     * @param int $id
     * @param string $name
     * @param string $domain
     * @param string $email
     * @param string $phone
     * @param string|null $logo
     * @param bool $isActive
     * @param Carbon $createdAt
     * @param Carbon $updatedAt
     */
    public function __construct(
        int $id,
        string $name,
        string $domain,
        string $email,
        string $phone,
        ?string $logo = null,
        bool $isActive = true,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->domain = $domain;
        $this->email = $email;
        $this->phone = $phone;
        $this->logo = $logo;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? now();
        $this->updatedAt = $updatedAt ?? now();
    }

    /**
     * Create a new store entity from an array.
     *
     * @param array $data
     * @param int|null $id
     * @return self
     */
    public static function fromArray(array $data, ?int $id = null): self
    {
        return new self(
            $id ?? ($data['id'] ?? 0),
            $data['name'],
            $data['domain'],
            $data['email'],
            $data['phone'],
            $data['logo'] ?? null,
            $data['is_active'] ?? true,
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
            'name' => $this->name,
            'domain' => $this->domain,
            'email' => $this->email,
            'phone' => $this->phone,
            'logo' => $this->logo,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->toDateTimeString(),
            'updated_at' => $this->updatedAt->toDateTimeString(),
        ];
    }

    /**
     * Get the store ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the store name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the store name.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the store domain.
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Set the store domain.
     *
     * @param string $domain
     * @return self
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the store email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the store email.
     *
     * @param string $email
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the store phone.
     *
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * Set the store phone.
     *
     * @param string $phone
     * @return self
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the store logo.
     *
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * Set the store logo.
     *
     * @param string|null $logo
     * @return self
     */
    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Check if the store is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Set the store active status.
     *
     * @param bool $isActive
     * @return self
     */
    public function setActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->updatedAt = now();
        return $this;
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
     * Update the store from an array of data.
     *
     * @param array $data
     * @return self
     */
    public function update(array $data): self
    {
        if (isset($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['domain'])) {
            $this->setDomain($data['domain']);
        }

        if (isset($data['email'])) {
            $this->setEmail($data['email']);
        }

        if (isset($data['phone'])) {
            $this->setPhone($data['phone']);
        }

        if (array_key_exists('logo', $data)) {
            $this->setLogo($data['logo']);
        }

        if (isset($data['is_active'])) {
            $this->setActive((bool) $data['is_active']);
        }

        return $this;
    }
    
    /**
     * Get the is_active attribute.
     *
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->isActive;
    }
    
    /**
     * Get the created_at attribute.
     *
     * @return Carbon
     */
    public function getCreatedAtAttribute(): Carbon
    {
        return $this->createdAt;
    }
    
    /**
     * Get the updated_at attribute.
     *
     * @return Carbon
     */
    public function getUpdatedAtAttribute(): Carbon
    {
        return $this->updatedAt;
    }
    
    /**
     * Convert the store entity to a string.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->id;
    }
    
    /**
     * Magic getter for properties.
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        // Direct property mapping
        if ($name === 'id') {
            return $this->id;
        }
        
        if ($name === 'name') {
            return $this->name;
        }
        
        if ($name === 'domain') {
            return $this->domain;
        }
        
        if ($name === 'email') {
            return $this->email;
        }
        
        if ($name === 'phone') {
            return $this->phone;
        }
        
        if ($name === 'logo') {
            return $this->logo;
        }
        
        if ($name === 'is_active') {
            return $this->isActive;
        }
        
        if ($name === 'created_at') {
            return $this->createdAt;
        }
        
        if ($name === 'updated_at') {
            return $this->updatedAt;
        }
        
        // Check for attribute getter methods
        if (method_exists($this, 'get' . ucfirst($name) . 'Attribute')) {
            $method = 'get' . ucfirst($name) . 'Attribute';
            return $this->$method();
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
        return isset($this->$offset) || method_exists($this, 'get' . ucfirst($offset) . 'Attribute');
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
