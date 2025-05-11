<?php

namespace Modules\Order\Entities;

use ArrayAccess;
use Illuminate\Support\Carbon;

class OrderItem implements ArrayAccess
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $orderId;

    /**
     * @var int
     */
    protected $productId;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var float
     */
    protected $subtotal;

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
    protected $productData;

    /**
     * OrderItem constructor.
     *
     * @param int $id
     * @param int $orderId
     * @param int $productId
     * @param int $quantity
     * @param float $price
     * @param float $subtotal
     * @param Carbon|null $createdAt
     * @param Carbon|null $updatedAt
     * @param array|null $productData
     */
    public function __construct(
        int $id,
        int $orderId,
        int $productId,
        int $quantity,
        float $price,
        float $subtotal,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null,
        ?array $productData = null
    ) {
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->subtotal = $subtotal;
        $this->createdAt = $createdAt ?? now();
        $this->updatedAt = $updatedAt ?? now();
        $this->productData = $productData;
    }

    /**
     * Create a new order item entity from an array.
     *
     * @param array $data
     * @param int|null $id
     * @return self
     */
    public static function fromArray(array $data, ?int $id = null): self
    {
        return new self(
            $id ?? ($data['id'] ?? 0),
            $data['order_id'],
            $data['product_id'],
            (int) $data['quantity'],
            (float) $data['price'],
            (float) $data['subtotal'],
            isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            $data['product_data'] ?? null
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
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'created_at' => $this->createdAt->toDateTimeString(),
            'updated_at' => $this->updatedAt->toDateTimeString(),
            'product_data' => $this->productData,
        ];
    }

    /**
     * Get the order item ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the order ID.
     *
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * Get the product ID.
     *
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * Get the quantity.
     *
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Set the quantity.
     *
     * @param int $quantity
     * @return self
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        $this->calculateSubtotal();
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the price.
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Set the price.
     *
     * @param float $price
     * @return self
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;
        $this->calculateSubtotal();
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the subtotal.
     *
     * @return float
     */
    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    /**
     * Calculate the subtotal.
     *
     * @return self
     */
    public function calculateSubtotal(): self
    {
        $this->subtotal = $this->price * $this->quantity;
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
     * Get the product data.
     *
     * @return array|null
     */
    public function getProductData(): ?array
    {
        return $this->productData;
    }

    /**
     * Set the product data.
     *
     * @param array|null $productData
     * @return self
     */
    public function setProductData(?array $productData): self
    {
        $this->productData = $productData;
        return $this;
    }

    /**
     * Get the product name attribute.
     *
     * @return string|null
     */
    public function getProductNameAttribute(): ?string
    {
        return $this->productData['name'] ?? null;
    }

    /**
     * Get the product SKU attribute.
     *
     * @return string|null
     */
    public function getProductSkuAttribute(): ?string
    {
        return $this->productData['sku'] ?? null;
    }

    /**
     * Convert the order item entity to a string.
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
        
        if ($name === 'order_id') {
            return $this->orderId;
        }
        
        if ($name === 'product_id') {
            return $this->productId;
        }
        
        if ($name === 'quantity') {
            return $this->quantity;
        }
        
        if ($name === 'price') {
            return $this->price;
        }
        
        if ($name === 'subtotal') {
            return $this->subtotal;
        }
        
        if ($name === 'created_at') {
            return $this->createdAt;
        }
        
        if ($name === 'updated_at') {
            return $this->updatedAt;
        }
        
        if ($name === 'product_name') {
            return $this->getProductNameAttribute();
        }
        
        if ($name === 'product_sku') {
            return $this->getProductSkuAttribute();
        }
        
        // Check for attribute getter methods
        if (method_exists($this, 'get' . ucfirst($name) . 'Attribute')) {
            $method = 'get' . ucfirst($name) . 'Attribute';
            return $this->$method();
        }
        
        // Check if it's in the productData array
        if (isset($this->productData) && is_array($this->productData) && isset($this->productData[$name])) {
            return $this->productData[$name];
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
               (isset($this->productData) && is_array($this->productData) && isset($this->productData[$offset]));
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
        
        // Check if it's in the productData array
        if (isset($this->productData) && is_array($this->productData) && isset($this->productData[$offset])) {
            return $this->productData[$offset];
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
