<?php

namespace Modules\Order\Entities;

use ArrayAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Order implements ArrayAccess
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
     * @var string
     */
    protected $orderNumber;

    /**
     * @var float
     */
    protected $totalAmount;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string|null
     */
    protected $shippingAddress;

    /**
     * @var string|null
     */
    protected $billingAddress;

    /**
     * @var string|null
     */
    protected $paymentMethod;

    /**
     * @var string
     */
    protected $paymentStatus;

    /**
     * @var string|null
     */
    protected $notes;

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
     * @var Collection|null
     */
    protected $items;

    /**
     * Order constructor.
     *
     * @param int $id
     * @param int $storeId
     * @param int $userId
     * @param string $orderNumber
     * @param float $totalAmount
     * @param string $status
     * @param string|null $shippingAddress
     * @param string|null $billingAddress
     * @param string|null $paymentMethod
     * @param string $paymentStatus
     * @param string|null $notes
     * @param Carbon|null $createdAt
     * @param Carbon|null $updatedAt
     * @param array|null $userData
     * @param Collection|null $items
     */
    public function __construct(
        int $id,
        int $storeId,
        int $userId,
        string $orderNumber,
        float $totalAmount,
        string $status = 'pending',
        ?string $shippingAddress = null,
        ?string $billingAddress = null,
        ?string $paymentMethod = null,
        string $paymentStatus = 'pending',
        ?string $notes = null,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null,
        ?array $userData = null,
        ?Collection $items = null
    ) {
        $this->id = $id;
        $this->storeId = $storeId;
        $this->userId = $userId;
        $this->orderNumber = $orderNumber;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->shippingAddress = $shippingAddress;
        $this->billingAddress = $billingAddress;
        $this->paymentMethod = $paymentMethod;
        $this->paymentStatus = $paymentStatus;
        $this->notes = $notes;
        $this->createdAt = $createdAt ?? now();
        $this->updatedAt = $updatedAt ?? now();
        $this->userData = $userData;
        $this->items = $items ?? collect();
    }

    /**
     * Create a new order entity from an array.
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
            $data['order_number'],
            (float) $data['total_amount'],
            $data['status'] ?? 'pending',
            $data['shipping_address'] ?? null,
            $data['billing_address'] ?? null,
            $data['payment_method'] ?? null,
            $data['payment_status'] ?? 'pending',
            $data['notes'] ?? null,
            isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            $data['user_data'] ?? null,
            isset($data['items']) ? collect($data['items']) : null
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
            'order_number' => $this->orderNumber,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'shipping_address' => $this->shippingAddress,
            'billing_address' => $this->billingAddress,
            'payment_method' => $this->paymentMethod,
            'payment_status' => $this->paymentStatus,
            'notes' => $this->notes,
            'created_at' => $this->createdAt->toDateTimeString(),
            'updated_at' => $this->updatedAt->toDateTimeString(),
            'user_data' => $this->userData,
            'items' => $this->items ? $this->items->toArray() : [],
        ];
    }

    /**
     * Get the order ID.
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
     * Get the order number.
     *
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * Get the total amount.
     *
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * Set the total amount.
     *
     * @param float $totalAmount
     * @return self
     */
    public function setTotalAmount(float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Calculate the total amount from items.
     *
     * @return self
     */
    public function calculateTotalAmount(): self
    {
        $this->totalAmount = $this->items->sum('subtotal');
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the status.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Set the status.
     *
     * @param string $status
     * @return self
     */
    public function setStatus(string $status): self
    {
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid order status');
        }
        
        $this->status = $status;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the shipping address.
     *
     * @return string|null
     */
    public function getShippingAddress(): ?string
    {
        return $this->shippingAddress;
    }

    /**
     * Set the shipping address.
     *
     * @param string|null $shippingAddress
     * @return self
     */
    public function setShippingAddress(?string $shippingAddress): self
    {
        $this->shippingAddress = $shippingAddress;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the billing address.
     *
     * @return string|null
     */
    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    /**
     * Set the billing address.
     *
     * @param string|null $billingAddress
     * @return self
     */
    public function setBillingAddress(?string $billingAddress): self
    {
        $this->billingAddress = $billingAddress;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the payment method.
     *
     * @return string|null
     */
    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    /**
     * Set the payment method.
     *
     * @param string|null $paymentMethod
     * @return self
     */
    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the payment status.
     *
     * @return string
     */
    public function getPaymentStatus(): string
    {
        return $this->paymentStatus;
    }

    /**
     * Set the payment status.
     *
     * @param string $paymentStatus
     * @return self
     */
    public function setPaymentStatus(string $paymentStatus): self
    {
        $validStatuses = ['pending', 'paid', 'failed', 'refunded'];
        
        if (!in_array($paymentStatus, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid payment status');
        }
        
        $this->paymentStatus = $paymentStatus;
        $this->updatedAt = now();
        return $this;
    }

    /**
     * Get the notes.
     *
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Set the notes.
     *
     * @param string|null $notes
     * @return self
     */
    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
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
     * Get the order items.
     *
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items ?? collect();
    }

    /**
     * Set the order items.
     *
     * @param Collection $items
     * @return self
     */
    public function setItems(Collection $items): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * Add an item to the order.
     *
     * @param OrderItem $item
     * @return self
     */
    public function addItem(OrderItem $item): self
    {
        $this->items->push($item);
        $this->calculateTotalAmount();
        return $this;
    }

    /**
     * Remove an item from the order.
     *
     * @param int $itemId
     * @return self
     */
    public function removeItem(int $itemId): self
    {
        $this->items = $this->items->reject(function ($item) use ($itemId) {
            return $item->getId() === $itemId;
        });
        
        $this->calculateTotalAmount();
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
     * Convert the order entity to a string.
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
        
        if ($name === 'user_id') {
            return $this->userId;
        }
        
        if ($name === 'order_number') {
            return $this->orderNumber;
        }
        
        if ($name === 'total_amount') {
            return $this->totalAmount;
        }
        
        if ($name === 'status') {
            return $this->status;
        }
        
        if ($name === 'shipping_address') {
            return $this->shippingAddress;
        }
        
        if ($name === 'billing_address') {
            return $this->billingAddress;
        }
        
        if ($name === 'payment_method') {
            return $this->paymentMethod;
        }
        
        if ($name === 'payment_status') {
            return $this->paymentStatus;
        }
        
        if ($name === 'notes') {
            return $this->notes;
        }
        
        if ($name === 'created_at') {
            return $this->createdAt;
        }
        
        if ($name === 'updated_at') {
            return $this->updatedAt;
        }
        
        if ($name === 'name') {
            return $this->getNameAttribute();
        }
        
        if ($name === 'email') {
            return $this->getEmailAttribute();
        }
        
        // Check for attribute getter methods
        if (method_exists($this, 'get' . ucfirst($name) . 'Attribute')) {
            $method = 'get' . ucfirst($name) . 'Attribute';
            return $this->$method();
        }
        
        // Check if it's in the userData array
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
