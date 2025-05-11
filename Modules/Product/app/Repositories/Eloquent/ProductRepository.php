<?php

namespace Modules\Product\app\Repositories\Eloquent;

use Modules\Product\app\Models\Product as ProductModel;
use Modules\Product\app\Repositories\Interfaces\ProductRepositoryInterface;
use Modules\Product\Entities\Product;
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
        try {
            $products = $this->model->all();
            return $this->transformCollection($products);
        } catch (\Exception $e) {
            Log::error('Error retrieving all products: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get all products with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->query();
            
            // Apply filters
            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->search($filters['search']);
            }
            
            if (isset($filters['store_id']) && !empty($filters['store_id'])) {
                $query->byStore($filters['store_id']);
            }
            
            if (isset($filters['min_price']) || isset($filters['max_price'])) {
                $query->byPriceRange(
                    $filters['min_price'] ?? null,
                    $filters['max_price'] ?? null
                );
            }
            
            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $products = $query->paginate($perPage);
            
            // Transform the data
            $products->getCollection()->transform(function ($product) {
                return $this->transformToEntity($product);
            });
            
            return $products;
        } catch (\Exception $e) {
            Log::error('Error paginating products: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Get products by store ID.
     *
     * @param int $storeId
     * @param array $filters
     * @return Collection
     */
    public function getByStoreId(int $storeId, array $filters = []): Collection
    {
        try {
            $query = $this->model->byStore($storeId);
            
            // Apply filters
            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->search($filters['search']);
            }
            
            if (isset($filters['min_price']) || isset($filters['max_price'])) {
                $query->byPriceRange(
                    $filters['min_price'] ?? null,
                    $filters['max_price'] ?? null
                );
            }
            
            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $products = $query->get();
            return $this->transformCollection($products);
        } catch (\Exception $e) {
            Log::error('Error retrieving products by store ID: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get products by store ID with pagination.
     *
     * @param int $storeId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateByStoreId(int $storeId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        try {
            $query = $this->model->byStore($storeId);
            
            // Apply filters
            if (isset($filters['search']) && !empty($filters['search'])) {
                $query->search($filters['search']);
            }
            
            if (isset($filters['min_price']) || isset($filters['max_price'])) {
                $query->byPriceRange(
                    $filters['min_price'] ?? null,
                    $filters['max_price'] ?? null
                );
            }
            
            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }
            
            // Apply sorting
            if (isset($filters['sort_by']) && !empty($filters['sort_by'])) {
                $sortDirection = $filters['sort_direction'] ?? 'asc';
                $query->orderBy($filters['sort_by'], $sortDirection);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $products = $query->paginate($perPage);
            
            // Transform the data
            $products->getCollection()->transform(function ($product) {
                return $this->transformToEntity($product);
            });
            
            return $products;
        } catch (\Exception $e) {
            Log::error('Error paginating products by store ID: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function find(int $id): ?Product
    {
        try {
            $product = $this->model->find($id);
            return $product ? $this->transformToEntity($product) : null;
        } catch (\Exception $e) {
            Log::error('Error finding product by ID: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Find a product by SKU.
     *
     * @param string $sku
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product
    {
        try {
            $product = $this->model->where('sku', $sku)->first();
            return $product ? $this->transformToEntity($product) : null;
        } catch (\Exception $e) {
            Log::error('Error finding product by SKU: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product
    {
        try {
            $product = $this->model->create($data);
            return $this->transformToEntity($product);
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update a product.
     *
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function update(int $id, array $data): Product
    {
        try {
            $product = $this->model->findOrFail($id);
            $product->update($data);
            return $this->transformToEntity($product->fresh());
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $product = $this->model->findOrFail($id);
            return $product->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search products by name or description.
     *
     * @param string $query
     * @param int|null $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, ?int $storeId = null, int $perPage = 15): LengthAwarePaginator
    {
        try {
            $searchQuery = $this->model->search($query);
            
            if ($storeId !== null) {
                $searchQuery->byStore($storeId);
            }
            
            $products = $searchQuery->paginate($perPage);
            
            // Transform the data
            $products->getCollection()->transform(function ($product) {
                return $this->transformToEntity($product);
            });
            
            return $products;
        } catch (\Exception $e) {
            Log::error('Error searching products: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Get active products.
     *
     * @param int|null $storeId
     * @return Collection
     */
    public function getActive(?int $storeId = null): Collection
    {
        try {
            $query = $this->model->active();
            
            if ($storeId !== null) {
                $query->byStore($storeId);
            }
            
            $products = $query->get();
            return $this->transformCollection($products);
        } catch (\Exception $e) {
            Log::error('Error retrieving active products: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Update product stock.
     *
     * @param int $id
     * @param int $quantity
     * @param bool $increase
     * @return Product
     */
    public function updateStock(int $id, int $quantity, bool $increase = true): Product
    {
        try {
            $product = $this->model->findOrFail($id);
            
            if ($increase) {
                $product->stock += $quantity;
            } else {
                if ($product->stock < $quantity) {
                    throw new \InvalidArgumentException('Not enough stock available');
                }
                $product->stock -= $quantity;
            }
            
            $product->save();
            return $this->transformToEntity($product->fresh());
        } catch (\Exception $e) {
            Log::error('Error updating product stock: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Transform a model to an entity.
     *
     * @param ProductModel $model
     * @return Product
     */
    protected function transformToEntity(ProductModel $model): Product
    {
        return Product::fromArray($model->toArray());
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
