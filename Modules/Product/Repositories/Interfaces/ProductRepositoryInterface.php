<?php

namespace Modules\Product\Repositories\Interfaces;

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
     * @return LengthAwarePaginator
     */
    public function getAllProducts(int $perPage = 10): LengthAwarePaginator;

    /**
     * Get products by store ID.
     *
     * @param int $storeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getProductsByStoreId(int $storeId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Find product by ID.
     *
     * @param int $id
     * @return Product|null
     */
    public function findById(int $id): ?Product;

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function createProduct(array $data): Product;

    /**
     * Update an existing product.
     *
     * @param int $id
     * @param array $data
     * @return Product|null
     */
    public function updateProduct(int $id, array $data): ?Product;

    /**
     * Update product stock.
     *
     * @param int $id
     * @param int $stock
     * @return Product|null
     */
    public function updateProductStock(int $id, int $stock): ?Product;

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function deleteProduct(int $id): bool;

    /**
     * Search for products.
     *
     * @param string $query
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchProducts(string $query, int $perPage = 10): LengthAwarePaginator;
}
