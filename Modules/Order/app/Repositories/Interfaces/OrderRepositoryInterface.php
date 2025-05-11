<?php

namespace Modules\Order\app\Repositories\Interfaces;

use Modules\Order\Entities\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    /**
     * Get all orders.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Get all orders with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get orders by store ID.
     *
     * @param int $storeId
     * @param array $filters
     * @return Collection
     */
    public function getByStoreId(int $storeId, array $filters = []): Collection;

    /**
     * Get orders by store ID with pagination.
     *
     * @param int $storeId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateByStoreId(int $storeId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get orders by user ID.
     *
     * @param int $userId
     * @param array $filters
     * @return Collection
     */
    public function getByUserId(int $userId, array $filters = []): Collection;

    /**
     * Get orders by user ID with pagination.
     *
     * @param int $userId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateByUserId(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find an order by ID.
     *
     * @param int $id
     * @return Order|null
     */
    public function find(int $id): ?Order;

    /**
     * Find an order by order number.
     *
     * @param string $orderNumber
     * @return Order|null
     */
    public function findByOrderNumber(string $orderNumber): ?Order;

    /**
     * Create a new order.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order;

    /**
     * Update an order.
     *
     * @param int $id
     * @param array $data
     * @return Order
     */
    public function update(int $id, array $data): Order;

    /**
     * Delete an order.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Update order status.
     *
     * @param int $id
     * @param string $status
     * @return Order
     */
    public function updateStatus(int $id, string $status): Order;

    /**
     * Update payment status.
     *
     * @param int $id
     * @param string $paymentStatus
     * @return Order
     */
    public function updatePaymentStatus(int $id, string $paymentStatus): Order;

    /**
     * Get orders by status.
     *
     * @param string $status
     * @param int|null $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByStatus(string $status, ?int $storeId = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get orders by payment status.
     *
     * @param string $paymentStatus
     * @param int|null $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByPaymentStatus(string $paymentStatus, ?int $storeId = null, int $perPage = 15): LengthAwarePaginator;
}
