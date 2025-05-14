<?php

namespace Modules\Order\Repositories\Eloquent;

use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderModel;
use Modules\Order\Events\OrderCreated;
use Modules\Order\Repositories\Interfaces\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var OrderModel
     */
    protected $model;

    /**
     * OrderRepository constructor.
     *
     * @param OrderModel $model
     */
    public function __construct(OrderModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get all orders.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get all orders with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllOrders(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->with(['items.product', 'store'])->paginate($perPage);
    }

    /**
     * Get orders by store ID.
     *
     * @param int $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrdersByStoreId(int $storeId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('store_id', $storeId)
            ->with(['items.product', 'store'])
            ->paginate($perPage);
    }

    /**
     * Get orders by customer email.
     *
     * @param string $email
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrdersByCustomerEmail(string $email, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('customer_email', $email)
            ->with(['items.product', 'store'])
            ->paginate($perPage);
    }

    /**
     * Get orders by status.
     *
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrdersByStatus(string $status, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('status', $status)
            ->with(['items.product', 'store'])
            ->paginate($perPage);
    }

    /**
     * Find order by ID.
     *
     * @param int $id
     * @return Order|null
     */
    public function findById(int $id): ?Order
    {
        $orderModel = $this->model->with(['items.product', 'store'])->find($id);

        if (!$orderModel) {
            return null;
        }

        return $this->mapModelToEntity($orderModel);
    }

    /**
     * Create a new order.
     *
     * @param array $data
     * @return Order
     */
    public function createOrder(array $data): Order
    {
        try {
            // Calculate total amount if not provided
            if (!isset($data['total_amount']) && isset($data['items'])) {
                $totalAmount = 0;
                foreach ($data['items'] as $item) {
                    $totalAmount += $item['quantity'] * $item['price'];
                }
                $data['total_amount'] = $totalAmount;
            }

            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = 'pending';
            }
            
            // Set default payment status if not provided
            if (!isset($data['payment_status'])) {
                $data['payment_status'] = 'pending';
            }
            
            // Generate order number if not provided
            if (!isset($data['order_number'])) {
                $data['order_number'] = 'ORD-' . time() . '-' . rand(1000, 9999);
            }
            
            // Ensure all required fields are present
            $requiredFields = [
                'shipping_address', 'billing_address', 'store_id',
                'user_id', 'total_amount', 'status', 'payment_status', 'order_number'
            ];
            
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new \Exception("Missing required field: {$field}");
                }
            }

            // Create the order
            $orderModel = $this->model->create($data);
            
            // Create order items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $orderModel->items()->create($item);
                }
            }
            
            // Load the relationships
            $orderModel->load(['items.product', 'store']);
            
            // Map the Eloquent model to a value object entity
            $orderEntity = $this->mapModelToEntity($orderModel);
            
            // Dispatch the OrderCreated event with the order entity
            event(new OrderCreated($orderEntity));
            
            return $orderEntity;
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing order.
     *
     * @param int $id
     * @param array $data
     * @return Order|null
     */
    public function updateOrder(int $id, array $data): ?Order
    {
        try {
            $orderModel = $this->model->find($id);

            if (!$orderModel) {
                return null;
            }

            $orderModel->update($data);

            return $this->mapModelToEntity($orderModel->fresh());
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update order status.
     *
     * @param int $id
     * @param string $status
     * @return Order|null
     */
    public function updateOrderStatus(int $id, string $status): ?Order
    {
        try {
            $orderModel = $this->model->find($id);

            if (!$orderModel) {
                return null;
            }

            $orderModel->update(['status' => $status]);

            return $this->mapModelToEntity($orderModel->fresh());
        } catch (\Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update order payment status.
     *
     * @param int $id
     * @param string $paymentStatus
     * @return Order|null
     */
    public function updateOrderPaymentStatus(int $id, string $paymentStatus): ?Order
    {
        try {
            $orderModel = $this->model->find($id);

            if (!$orderModel) {
                return null;
            }

            $orderModel->update(['payment_status' => $paymentStatus]);

            return $this->mapModelToEntity($orderModel->fresh());
        } catch (\Exception $e) {
            Log::error('Error updating order payment status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an order.
     *
     * @param int $id
     * @return bool
     */
    public function deleteOrder(int $id): bool
    {
        try {
            $orderModel = $this->model->find($id);

            if (!$orderModel) {
                return false;
            }

            // Delete associated order items
            $orderModel->items()->delete();

            // Delete the order
            return $orderModel->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get order statistics.
     *
     * @param int|null $storeId
     * @return array
     */
    public function getOrderStatistics(?int $storeId = null): array
    {
        try {
            $query = $this->model;

            if ($storeId) {
                $query = $query->where('store_id', $storeId);
            }

            $totalOrders = $query->count();
            $totalRevenue = $query->sum('total_amount');
            $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

            return [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'average_order_value' => $averageOrderValue,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting order statistics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get order count by status.
     *
     * @param int|null $storeId
     * @return array
     */
    public function getOrderCountByStatus(?int $storeId = null): array
    {
        try {
            $query = $this->model;

            if ($storeId) {
                $query = $query->where('store_id', $storeId);
            }

            return $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting order count by status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get revenue by date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int|null $storeId
     * @return array
     */
    public function getRevenueByDateRange(string $startDate, string $endDate, ?int $storeId = null): array
    {
        try {
            $query = $this->model->whereBetween('created_at', [$startDate, $endDate]);

            if ($storeId) {
                $query = $query->where('store_id', $storeId);
            }

            return $query->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->keyBy('date')
                ->map(function ($item) {
                    return $item->revenue;
                })
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Error getting revenue by date range: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Map a order model to a order entity.
     *
     * @param OrderModel $model
     * @return Order
     */
    protected function mapModelToEntity(OrderModel $model): Order
    {
        // Extract user data if available
        $userData = null;
        if ($model->user) {
            $userData = [
                'id' => $model->user_id,
                'name' => $model->customer_name ?? '',
                'email' => $model->customer_email ?? ''
            ];
        }
        
        // Create items collection if available
        $items = null;
        if ($model->relationLoaded('items')) {
            $items = $model->items;
        }
        
        // Create a new Order entity with required constructor parameters
        return new Order(
            $model->id,
            $model->store_id,
            $model->user_id ?? 0,
            $model->order_number ?? ('ORD-' . $model->id),
            (float) $model->total_amount,
            $model->status ?? 'pending',
            $model->shipping_address,
            $model->billing_address,
            $model->payment_method,
            $model->payment_status ?? 'pending',
            $model->notes,
            $model->created_at,
            $model->updated_at,
            $userData,
            $items
        );
    }
}
