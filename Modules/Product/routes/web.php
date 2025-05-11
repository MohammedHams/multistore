<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\app\Http\Controllers\ProductController;

// Define the controller once to avoid duplicate class loading
$productController = ProductController::class;

// Routes with original names but protected by admin guard
Route::middleware(['auth', 'verified', 'admin'])->group(function () use ($productController) {
    Route::resource('products', $productController)->names('product');
    Route::put('products/{id}/stock', [$productController, 'updateStock'])->name('product.update.stock');
});

// Admin routes - full access to all products
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () use ($productController) {
    Route::resource('products', $productController)->names('admin.product');
    Route::put('products/{id}/stock', [$productController, 'updateStock'])->name('admin.product.update.stock');
});

// Store Owner routes - access only to their store's products
Route::middleware(['auth', 'verified', 'store-owner'])->prefix('store-owner')->group(function () use ($productController) {
    Route::resource('products', $productController)->names('store-owner.product');
    Route::put('products/{id}/stock', [$productController, 'updateStock'])->name('store-owner.product.update.stock');
});

// Store Staff routes - access based on permissions
Route::middleware(['auth', 'verified', 'store-staff'])->prefix('store-staff')->group(function () use ($productController) {
    Route::resource('products', $productController)->names('store-staff.product');
    Route::put('products/{id}/stock', [$productController, 'updateStock'])->name('store-staff.product.update.stock');
});
