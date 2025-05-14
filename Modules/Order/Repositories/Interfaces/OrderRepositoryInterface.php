<?php

namespace Modules\Order\Repositories\Interfaces;

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
     * @return LengthAwarePaginator
     */
    public function getAllOrders(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get orders by store ID.
     *
     * @param int $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrdersByStoreId(int $storeId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get orders by customer email.
     *
     * @param string $email
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrdersByCustomerEmail(string $email, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get orders by status.
     *
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrdersByStatus(string $status, int $perPage = 10): LengthAwarePaginator;

    /**
     * Find order by ID.
     *
     * @param int $id
     * @return Order|null
     */
    public function findById(int $id): ?Order;

    /**
     * Create a new order.
     *
     * @param array $data
     * @return Order
     */
    public function createOrder(array $data): Order;

    /**
     * Update an existing order.
     *
     * @param int $id
     * @param array $data
     * @return Order|null
     */
    public function updateOrder(int $id, array $data): ?Order;

    /**
     * Update order status.
     *
     * @param int $id
     * @param string $status
     * @return Order|null
     */
    public function updateOrderStatus(int $id, string $status): ?Order;
    
    /**
     * Update order payment status.
     *
     * @param int $id
     * @param string $paymentStatus
     * @return Order|null
     */
    public function updateOrderPaymentStatus(int $id, string $paymentStatus): ?Order;

    /**
     * Delete an order.
     *
     * @param int $id
     * @return bool
     */
    public function deleteOrder(int $id): bool;

    /**
     * Get order statistics.
     *
     * @param int|null $storeId
     * @return array
     */
    public function getOrderStatistics(?int $storeId = null): array;

    /**
     * Get order count by status.
     *
     * @param int|null $storeId
     * @return array
     */
    public function getOrderCountByStatus(?int $storeId = null): array;

    /**
     * Get revenue by date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getRevenueByDateRange(string $startDate, string $endDate, ?int $storeId = null): array;
}
