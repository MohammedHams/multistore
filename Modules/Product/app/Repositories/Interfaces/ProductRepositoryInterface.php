<?php

namespace Modules\Product\app\Repositories\Interfaces;

use Modules\Product\Entities\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get all products.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Get all products with pagination.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Get products by store ID.
     *
     * @param int $storeId
     * @param array $filters
     * @return Collection
     */
    public function getByStoreId(int $storeId, array $filters = []): Collection;

    /**
     * Get products by store ID with pagination.
     *
     * @param int $storeId
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateByStoreId(int $storeId, int $perPage = 15, array $filters = []): LengthAwarePaginator;

    /**
     * Find a product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function find(int $id): ?Product;

    /**
     * Find a product by SKU.
     *
     * @param string $sku
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function create(array $data): Product;

    /**
     * Update a product.
     *
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function update(int $id, array $data): Product;

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * Search products by name or description.
     *
     * @param string $query
     * @param int|null $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $query, ?int $storeId = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get active products.
     *
     * @param int|null $storeId
     * @return Collection
     */
    public function getActive(?int $storeId = null): Collection;

    /**
     * Update product stock.
     *
     * @param int $id
     * @param int $quantity
     * @param bool $increase
     * @return Product
     */
    public function updateStock(int $id, int $quantity, bool $increase = true): Product;
}
