<?php

namespace Modules\Order\app\Repositories\Eloquent;

use Modules\Order\app\Models\Order as OrderModel;
use Modules\Order\app\Repositories\Interfaces\OrderRepositoryInterface;
use Modules\Order\Entities\Order;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        try {
            $orders = $this->model->with(['items.product', 'user', 'store'])->get();
            return $this->transformCollection($orders);
        } catch (\Exception $e) {
            Log::error('Error retrieving all orders: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get all orders with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->with(['items.product', 'user', 'store']);
            
            // Apply filters
            if (isset($filters['status']) && !empty($filters['status'])) {
                $query->byStatus($filters['status']);
            }
            
            if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
                $query->byPaymentStatus($filters['payment_status']);
            }
            
            if (isset($filters['store_id']) && !empty($filters['store_id'])) {
                $query->byStore($filters['store_id']);
            }
            
            if (isset($filters['user_id']) && !empty($filters['user_id'])) {
                $query->byUser($filters['user_id']);
            }
            
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->byDateRange($filters['start_date'], $filters['end_date']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $orders = $query->paginate($perPage);
            
            // Transform the data
            $orders->getCollection()->transform(function ($order) {
                return $this->transformToEntity($order);
            });
            
            return $orders;
        } catch (\Exception $e) {
            Log::error('Error paginating orders: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Get orders by store ID.
     *
     * @param int $storeId
     * @param array $filters
     * @return Collection
     */
    public function getByStoreId(int $storeId, array $filters = []): Collection
    {
        try {
            $query = $this->model->with(['items.product', 'user', 'store'])
                ->byStore($storeId);
            
            // Apply filters
            if (isset($filters['status']) && !empty($filters['status'])) {
                $query->byStatus($filters['status']);
            }
            
            if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
                $query->byPaymentStatus($filters['payment_status']);
            }
            
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->byDateRange($filters['start_date'], $filters['end_date']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $orders = $query->get();
            return $this->transformCollection($orders);
        } catch (\Exception $e) {
            Log::error('Error retrieving orders by store ID: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get orders by store ID with pagination.
     *
     * @param int $storeId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateByStoreId(int $storeId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->with(['items.product', 'user', 'store'])
                ->byStore($storeId);
            
            // Apply filters
            if (isset($filters['status']) && !empty($filters['status'])) {
                $query->byStatus($filters['status']);
            }
            
            if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
                $query->byPaymentStatus($filters['payment_status']);
            }
            
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->byDateRange($filters['start_date'], $filters['end_date']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $orders = $query->paginate($perPage);
            
            // Transform the data
            $orders->getCollection()->transform(function ($order) {
                return $this->transformToEntity($order);
            });
            
            return $orders;
        } catch (\Exception $e) {
            Log::error('Error paginating orders by store ID: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Get orders by user ID.
     *
     * @param int $userId
     * @param array $filters
     * @return Collection
     */
    public function getByUserId(int $userId, array $filters = []): Collection
    {
        try {
            $query = $this->model->with(['items.product', 'user', 'store'])
                ->byUser($userId);
            
            // Apply filters
            if (isset($filters['status']) && !empty($filters['status'])) {
                $query->byStatus($filters['status']);
            }
            
            if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
                $query->byPaymentStatus($filters['payment_status']);
            }
            
            if (isset($filters['store_id']) && !empty($filters['store_id'])) {
                $query->byStore($filters['store_id']);
            }
            
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->byDateRange($filters['start_date'], $filters['end_date']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $orders = $query->get();
            return $this->transformCollection($orders);
        } catch (\Exception $e) {
            Log::error('Error retrieving orders by user ID: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get orders by user ID with pagination.
     *
     * @param int $userId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateByUserId(int $userId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->with(['items.product', 'user', 'store'])
                ->byUser($userId);
            
            // Apply filters
            if (isset($filters['status']) && !empty($filters['status'])) {
                $query->byStatus($filters['status']);
            }
            
            if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
                $query->byPaymentStatus($filters['payment_status']);
            }
            
            if (isset($filters['store_id']) && !empty($filters['store_id'])) {
                $query->byStore($filters['store_id']);
            }
            
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $query->byDateRange($filters['start_date'], $filters['end_date']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $orders = $query->paginate($perPage);
            
            // Transform the data
            $orders->getCollection()->transform(function ($order) {
                return $this->transformToEntity($order);
            });
            
            return $orders;
        } catch (\Exception $e) {
            Log::error('Error paginating orders by user ID: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Find an order by ID.
     *
     * @param int $id
     * @return Order|null
     */
    public function find(int $id): ?Order
    {
        try {
            $order = $this->model->with(['items.product', 'user', 'store'])->find($id);
            return $order ? $this->transformToEntity($order) : null;
        } catch (\Exception $e) {
            Log::error('Error finding order by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find an order by order number.
     *
     * @param string $orderNumber
     * @return Order|null
     */
    public function findByOrderNumber(string $orderNumber): ?Order
    {
        try {
            $order = $this->model->with(['items.product', 'user', 'store'])
                ->where('order_number', $orderNumber)
                ->first();
            return $order ? $this->transformToEntity($order) : null;
        } catch (\Exception $e) {
            Log::error('Error finding order by order number: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new order.
     *
     * @param array $data
     * @return Order
     */
    public function create(array $data): Order
    {
        try {
            // Generate order number if not provided
            if (!isset($data['order_number']) || empty($data['order_number'])) {
                $data['order_number'] = OrderModel::generateOrderNumber();
            }
            
            $order = $this->model->create($data);
            
            // Create order items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create($item);
                }
            }
            
            // Refresh the order with items
            $order = $order->fresh(['items.product', 'user', 'store']);
            
            return $this->transformToEntity($order);
        } catch (\Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an order.
     *
     * @param int $id
     * @param array $data
     * @return Order
     */
    public function update(int $id, array $data): Order
    {
        try {
            $order = $this->model->findOrFail($id);
            $order->update($data);
            
            // Refresh the order with items
            $order = $order->fresh(['items.product', 'user', 'store']);
            
            return $this->transformToEntity($order);
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an order.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $order = $this->model->findOrFail($id);
            
            // Delete order items first
            $order->items()->delete();
            
            return $order->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order status.
     *
     * @param int $id
     * @param string $status
     * @return Order
     */
    public function updateStatus(int $id, string $status): Order
    {
        try {
            $order = $this->model->findOrFail($id);
            $order->status = $status;
            $order->save();
            
            // Refresh the order with items
            $order = $order->fresh(['items.product', 'user', 'store']);
            
            return $this->transformToEntity($order);
        } catch (\Exception $e) {
            Log::error('Error updating order status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update payment status.
     *
     * @param int $id
     * @param string $paymentStatus
     * @return Order
     */
    public function updatePaymentStatus(int $id, string $paymentStatus): Order
    {
        try {
            $order = $this->model->findOrFail($id);
            $order->payment_status = $paymentStatus;
            $order->save();
            
            // Refresh the order with items
            $order = $order->fresh(['items.product', 'user', 'store']);
            
            return $this->transformToEntity($order);
        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get orders by status.
     *
     * @param string $status
     * @param int|null $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByStatus(string $status, ?int $storeId = null, int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = $this->model->with(['items.product', 'user', 'store'])
                ->byStatus($status);
            
            if ($storeId !== null) {
                $query->byStore($storeId);
            }
            
            $orders = $query->paginate($perPage);
            
            // Transform the data
            $orders->getCollection()->transform(function ($order) {
                return $this->transformToEntity($order);
            });
            
            return $orders;
        } catch (\Exception $e) {
            Log::error('Error retrieving orders by status: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Get orders by payment status.
     *
     * @param string $paymentStatus
     * @param int|null $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByPaymentStatus(string $paymentStatus, ?int $storeId = null, int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = $this->model->with(['items.product', 'user', 'store'])
                ->byPaymentStatus($paymentStatus);
            
            if ($storeId !== null) {
                $query->byStore($storeId);
            }
            
            $orders = $query->paginate($perPage);
            
            // Transform the data
            $orders->getCollection()->transform(function ($order) {
                return $this->transformToEntity($order);
            });
            
            return $orders;
        } catch (\Exception $e) {
            Log::error('Error retrieving orders by payment status: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Transform a model to an entity.
     *
     * @param OrderModel $model
     * @return Order
     */
    protected function transformToEntity(OrderModel $model): Order
    {
        $data = $model->toArray();
        
        // Add user data
        if ($model->user) {
            $data['user_data'] = $model->user->toArray();
        }
        
        // Transform items
        if ($model->items) {
            $items = collect();
            foreach ($model->items as $item) {
                $itemData = $item->toArray();
                
                // Add product data
                if ($item->product) {
                    $itemData['product_data'] = $item->product->toArray();
                }
                
                $items->push($itemData);
            }
            $data['items'] = $items->toArray();
        }
        
        return Order::fromArray($data);
    }

    /**
     * Transform a collection of models to a collection of entities.
     *
     * @param Collection $models
     * @return Collection
     */
    protected function transformCollection(Collection $models): Collection
    {
        return $models->map(function ($model) {
            return $this->transformToEntity($model);
        });
    }
}
