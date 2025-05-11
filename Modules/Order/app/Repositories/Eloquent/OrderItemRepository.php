<?php

namespace Modules\Order\app\Repositories\Eloquent;

use Modules\Order\app\Models\OrderItem as OrderItemModel;
use Modules\Order\app\Repositories\Interfaces\OrderItemRepositoryInterface;
use Modules\Order\Entities\OrderItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class OrderItemRepository implements OrderItemRepositoryInterface
{
    /**
     * @var OrderItemModel
     */
    protected $model;

    /**
     * OrderItemRepository constructor.
     *
     * @param OrderItemModel $model
     */
    public function __construct(OrderItemModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get all order items.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        try {
            $orderItems = $this->model->with(['order', 'product'])->get();
            return $this->transformCollection($orderItems);
        } catch (\Exception $e) {
            Log::error('Error retrieving all order items: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get order items by order ID.
     *
     * @param int $orderId
     * @return Collection
     */
    public function getByOrderId(int $orderId): Collection
    {
        try {
            $orderItems = $this->model->with(['product'])
                ->where('order_id', $orderId)
                ->get();
            return $this->transformCollection($orderItems);
        } catch (\Exception $e) {
            Log::error('Error retrieving order items by order ID: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get order items by product ID.
     *
     * @param int $productId
     * @return Collection
     */
    public function getByProductId(int $productId): Collection
    {
        try {
            $orderItems = $this->model->with(['order'])
                ->where('product_id', $productId)
                ->get();
            return $this->transformCollection($orderItems);
        } catch (\Exception $e) {
            Log::error('Error retrieving order items by product ID: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Find an order item by ID.
     *
     * @param int $id
     * @return OrderItem|null
     */
    public function find(int $id): ?OrderItem
    {
        try {
            $orderItem = $this->model->with(['order', 'product'])->find($id);
            return $orderItem ? $this->transformToEntity($orderItem) : null;
        } catch (\Exception $e) {
            Log::error('Error finding order item by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function create(array $data): OrderItem
    {
        try {
            $orderItem = $this->model->create($data);
            return $this->transformToEntity($orderItem->fresh(['order', 'product']));
        } catch (\Exception $e) {
            Log::error('Error creating order item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an order item.
     *
     * @param int $id
     * @param array $data
     * @return OrderItem
     */
    public function update(int $id, array $data): OrderItem
    {
        try {
            $orderItem = $this->model->findOrFail($id);
            $orderItem->update($data);
            return $this->transformToEntity($orderItem->fresh(['order', 'product']));
        } catch (\Exception $e) {
            Log::error('Error updating order item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an order item.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $orderItem = $this->model->findOrFail($id);
            return $orderItem->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting order item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order item quantity.
     *
     * @param int $id
     * @param int $quantity
     * @return OrderItem
     */
    public function updateQuantity(int $id, int $quantity): OrderItem
    {
        try {
            $orderItem = $this->model->findOrFail($id);
            $orderItem->quantity = $quantity;
            $orderItem->save();
            return $this->transformToEntity($orderItem->fresh(['order', 'product']));
        } catch (\Exception $e) {
            Log::error('Error updating order item quantity: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create multiple order items at once.
     *
     * @param array $items
     * @return Collection
     */
    public function createMany(array $items): Collection
    {
        try {
            $createdItems = collect();
            
            foreach ($items as $itemData) {
                $item = $this->model->create($itemData);
                $createdItems->push($item);
            }
            
            // Refresh all items with relationships
            $ids = $createdItems->pluck('id')->toArray();
            $refreshedItems = $this->model->with(['order', 'product'])
                ->whereIn('id', $ids)
                ->get();
            
            return $this->transformCollection($refreshedItems);
        } catch (\Exception $e) {
            Log::error('Error creating multiple order items: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transform a model to an entity.
     *
     * @param OrderItemModel $model
     * @return OrderItem
     */
    protected function transformToEntity(OrderItemModel $model): OrderItem
    {
        $data = $model->toArray();
        
        // Add product data
        if ($model->product) {
            $data['product_data'] = $model->product->toArray();
        }
        
        return OrderItem::fromArray($data);
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
