<?php

namespace Modules\Order\app\Repositories\Interfaces;

use Modules\Order\Entities\OrderItem;
use Illuminate\Support\Collection;

interface OrderItemRepositoryInterface
{
    /**
     * Get all order items.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Get order items by order ID.
     *
     * @param int $orderId
     * @return Collection
     */
    public function getByOrderId(int $orderId): Collection;

    /**
     * Get order items by product ID.
     *
     * @param int $productId
     * @return Collection
     */
    public function getByProductId(int $productId): Collection;

    /**
     * Find an order item by ID.
     *
     * @param int $id
     * @return OrderItem|null
     */
    public function find(int $id): ?OrderItem;

    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function create(array $data): OrderItem;

    /**
     * Update an order item.
     *
     * @param int $id
     * @param array $data
     * @return OrderItem
     */
    public function update(int $id, array $data): OrderItem;

    /**
     * Delete an order item.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Update order item quantity.
     *
     * @param int $id
     * @param int $quantity
     * @return OrderItem
     */
    public function updateQuantity(int $id, int $quantity): OrderItem;

    /**
     * Create multiple order items at once.
     *
     * @param array $items
     * @return Collection
     */
    public function createMany(array $items): Collection;
}
