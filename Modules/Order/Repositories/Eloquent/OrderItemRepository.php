<?php

namespace Modules\Order\Repositories\Eloquent;

use Modules\Order\Entities\OrderItem;
use Modules\Order\Entities\OrderItemModel;
use Modules\Order\Repositories\Interfaces\OrderItemRepositoryInterface;
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
        return $this->model->all();
    }

    /**
     * Get order items by order ID.
     *
     * @param int $orderId
     * @return Collection
     */
    public function getOrderItems(int $orderId): Collection
    {
        return $this->model->where('order_id', $orderId)
            ->with('product')
            ->get();
    }

    /**
     * Get order items by product ID.
     *
     * @param int $productId
     * @return Collection
     */
    public function getByProductId(int $productId): Collection
    {
        return $this->model->where('product_id', $productId)
            ->with('order')
            ->get();
    }

    /**
     * Find order item by ID.
     *
     * @param int $id
     * @return OrderItem|null
     */
    public function findById(int $id): ?OrderItem
    {
        $orderItemModel = $this->model->with(['order', 'product'])->find($id);

        if (!$orderItemModel) {
            return null;
        }

        return $this->mapModelToEntity($orderItemModel);
    }

    /**
     * Create a new order item.
     *
     * @param array $data
     * @return OrderItem
     */
    public function createOrderItem(array $data): OrderItem
    {
        try {
            // Calculate subtotal if not provided
            if (!isset($data['subtotal']) && isset($data['quantity']) && isset($data['price'])) {
                $data['subtotal'] = $data['quantity'] * $data['price'];
            }

            // Create the order item
            $orderItemModel = $this->model->create($data);

            return $this->mapModelToEntity($orderItemModel);
        } catch (\Exception $e) {
            Log::error('Error creating order item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing order item.
     *
     * @param int $id
     * @param array $data
     * @return OrderItem|null
     */
    public function updateOrderItem(int $id, array $data): ?OrderItem
    {
        try {
            $orderItemModel = $this->model->find($id);

            if (!$orderItemModel) {
                return null;
            }

            // Calculate total if not provided
            if (!isset($data['total']) && isset($data['quantity']) && isset($data['price'])) {
                $data['total'] = $data['quantity'] * $data['price'];
            }

            $orderItemModel->update($data);

            return $this->mapModelToEntity($orderItemModel->fresh());
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
    public function deleteOrderItem(int $id): bool
    {
        try {
            $orderItemModel = $this->model->find($id);

            if (!$orderItemModel) {
                return false;
            }

            return $orderItemModel->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting order item: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get total amount for an order.
     *
     * @param int $orderId
     * @return float
     */
    public function getTotalAmount(int $orderId): float
    {
        return $this->model->where('order_id', $orderId)->sum('total');
    }

    /**
     * Get total quantity for an order.
     *
     * @param int $orderId
     * @return int
     */
    public function getTotalQuantity(int $orderId): int
    {
        return $this->model->where('order_id', $orderId)->sum('quantity');
    }

    /**
     * Map a order item model to a order item entity.
     *
     * @param OrderItemModel $model
     * @return OrderItem
     */
    protected function mapModelToEntity(OrderItemModel $model): OrderItem
    {
        // Calculate subtotal from price and quantity
        $subtotal = $model->price * $model->quantity;
        
        // Create the OrderItem entity
        $orderItem = new OrderItem(
            $model->id,
            $model->order_id,
            $model->product_id,
            $model->quantity,
            $model->price,
            $subtotal,
            $model->created_at,
            $model->updated_at
        );

        // Set additional relationships if loaded
        if ($model->relationLoaded('product') && $model->product) {
            $productData = [
                'name' => $model->product->name,
                'sku' => $model->product->sku ?? '',
                'price' => $model->product->price
            ];
            $orderItem->setProductData($productData);
        }

        return $orderItem;
    }
}
