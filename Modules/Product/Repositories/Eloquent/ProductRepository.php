<?php

namespace Modules\Product\Repositories\Eloquent;

use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductModel;
use Modules\Product\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var ProductModel
     */
    protected $model;
    
    /**
     * @var Product
     */
    protected $entity;

    /**
     * ProductRepository constructor.
     *
     * @param ProductModel $model
     */
    public function __construct(ProductModel $model)
    {
        $this->model = $model;
    }

    /**
     * Get all products.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get all products with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllProducts(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Get products by store ID.
     *
     * @param int $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProductsByStoreId(int $storeId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('store_id', $storeId)->paginate($perPage);
    }

    /**
     * Find product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product
    {
        $productModel = $this->model->find($id);
        
        if (!$productModel) {
            return null;
        }
        
        return Product::fromArray($productModel->toArray(), $productModel->id);
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product
    {
        try {
            $productModel = $this->model->create($data);
            
            // Map the ProductModel to a Product entity
            return Product::fromArray($productModel->toArray(), $productModel->id);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param array $data
     * @return Product|null
     */
    public function updateProduct(int $id, array $data): ?Product
    {
        try {
            $productModel = $this->model->find($id);
            
            if (!$productModel) {
                return null;
            }
            
            $productModel->update($data);
            $productModel = $productModel->fresh();
            
            return Product::fromArray($productModel->toArray(), $productModel->id);
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update product stock.
     *
     * @param int $id
     * @param int $stock
     * @return Product|null
     */
    public function updateProductStock(int $id, int $stock): ?Product
    {
        try {
            $productModel = $this->model->find($id);
            
            if (!$productModel) {
                return null;
            }
            
            $productModel->stock = $stock;
            $productModel->save();
            $productModel = $productModel->fresh();
            
            return Product::fromArray($productModel->toArray(), $productModel->id);
        } catch (\Exception $e) {
            Log::error('Error updating product stock: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool
    {
        try {
            $productModel = $this->model->find($id);
            
            if (!$productModel) {
                return false;
            }
            
            return $productModel->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Search for products.
     *
     * @param string $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchProducts(string $query, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->paginate($perPage);
    }
}
