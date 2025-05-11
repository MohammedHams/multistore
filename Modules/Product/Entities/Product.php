<?php

namespace Modules\Product\Entities;

use ArrayAccess;
use Illuminate\Support\Carbon;

class Product implements ArrayAccess
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
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var int
     */
    protected $stock;

    /**
     * @var string
     */
    protected $sku;

    /**
     * @var string|null
     */
    protected $image;

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
     * Product constructor.
     *
     * @param int $id
     * @param int $storeId
     * @param string $name
     * @param string|null $description
     * @param float $price
     * @param int $stock
     * @param string $sku
     * @param string|null $image
     * @param bool $isActive
     * @param Carbon|null $createdAt
     * @param Carbon|null $updatedAt
     */
    public function __construct(
        int $id,
        int $storeId,
        string $name,
        ?string $description,
        float $price,
        int $stock,
        string $sku,
        ?string $image = null,
        bool $isActive = true,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null
    ) {
        $this->id = $id;
        $this->storeId = $storeId;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
        $this->sku = $sku;
        $this->image = $image;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? now();
        $this->updatedAt = $updatedAt ?? now();
    }

    /**
     * Create a new product entity from an array.
     *
     * @param array $data
     * @param int|null $id
     * @return self
     */
    public static function fromArray(array $data, ?int $id = null): self
    {
        return new self(
            $id ?? (isset($data['id']) ? (int)$data['id'] : 0),
            (int)$data['store_id'],
            $data['name'],
            $data['description'] ?? null,
            (float) $data['price'],
            (int) ($data['stock'] ?? 0),
            $data['sku'],
            $data['image'] ?? null,
            isset($data['is_active']) ? (bool)$data['is_active'] : true,
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
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'image' => $this->image,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->toDateTimeString(),
            'updated_at' => $this->updatedAt->toDateTimeString(),
        ];
    }

    /**
     * Get the product ID.
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
     * Get the product name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the product name.
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
     * Get the product description.
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the product description.
     *
     * @param string|null $description
     * @return self
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the product price.
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Set the product price.
     *
     * @param float $price
     * @return self
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the product stock.
     *
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * Set the product stock.
     *
     * @param int $stock
     * @return self
     */
    public function setStock(int $stock): self
    {
        $this->stock = $stock;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Decrease the product stock.
     *
     * @param int $quantity
     * @return self
     */
    public function decreaseStock(int $quantity): self
    {
        if ($quantity > $this->stock) {
            throw new \InvalidArgumentException('Not enough stock available');
        }
        
        $this->stock -= $quantity;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Increase the product stock.
     *
     * @param int $quantity
     * @return self
     */
    public function increaseStock(int $quantity): self
    {
        $this->stock += $quantity;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the product SKU.
     *
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * Set the product SKU.
     *
     * @param string $sku
     * @return self
     */
    public function setSku(string $sku): self
    {
        $this->sku = $sku;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the product image.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Set the product image.
     *
     * @param string|null $image
     * @return self
     */
    public function setImage(?string $image): self
    {
        $this->image = $image;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Check if the product is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * Set the product active status.
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
     * Convert the product entity to a string.
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
        
        if ($name === 'store_id') {
            return $this->storeId;
        }
        
        if ($name === 'name') {
            return $this->name;
        }
        
        if ($name === 'description') {
            return $this->description;
        }
        
        if ($name === 'price') {
            return $this->price;
        }
        
        if ($name === 'stock') {
            return $this->stock;
        }
        
        if ($name === 'sku') {
            return $this->sku;
        }
        
        if ($name === 'image') {
            return $this->image;
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
