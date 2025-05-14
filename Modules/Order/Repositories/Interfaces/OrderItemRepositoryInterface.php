<?php

namespace Modules\Order\Repositories\Interfaces;

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
    public function getOrderItems(int $orderId): Collection;

    /**
     * Get order items by product ID.
     *
     * @param int $productId
     * @return Collection
     */
    public function getByProductId(int $productId): Collection;

    /**
     * Find order item by ID.
     *
     * @param int $id
     * @return OrderItem|null
     */
    public function findById(int $id): ?OrderItem;

    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function createOrderItem(array $data): OrderItem;

    /**
     * Update an existing order item.
     *
     * @param int $id
     * @param array $data
     * @return OrderItem|null
     */
    public function updateOrderItem(int $id, array $data): ?OrderItem;

    /**
     * Delete an order item.
     *
     * @param int $id
     * @return bool
     */
    public function deleteOrderItem(int $id): bool;

    /**
     * Get total amount for an order.
     *
     * @param int $orderId
     * @return float
     */
    public function getTotalAmount(int $orderId): float;

    /**
     * Get total quantity for an order.
     *
     * @param int $orderId
     * @return int
     */
    public function getTotalQuantity(int $orderId): int;
}
