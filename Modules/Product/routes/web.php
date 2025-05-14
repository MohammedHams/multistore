<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;

// Admin routes - full access to all products
Route::middleware(['auth:admin', 'web'])->prefix('admin')->group(function () {
    // Main routes with admin.product prefix
    Route::get('products', [ProductController::class, 'index'])->name('admin.product.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('admin.product.create');
    Route::post('products', [ProductController::class, 'store'])->name('admin.product.store');
    Route::get('products/{id}', [ProductController::class, 'show'])->name('admin.product.show');
    Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('admin.product.edit');
    Route::put('products/{id}', [ProductController::class, 'update'])->name('admin.product.update');
    Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('admin.product.destroy');
    Route::put('products/{id}/stock', [ProductController::class, 'updateStock'])->name('admin.product.update.stock');
});

// Store Owner routes - access only to their store's products
Route::middleware(['auth:store-owner'])->prefix('store-owner')->group(function () {
    Route::resource('products', ProductController::class)->names('store-owner.product');
    Route::put('products/{id}/stock', [ProductController::class, 'updateStock'])->name('store-owner.product.update.stock');
});

// Store Staff routes
Route::middleware(['auth:store-staff'])->prefix('store-staff')->group(function () {
    // View products
    Route::get('products', [ProductController::class, 'index'])->name('store-staff.product.index');
    Route::get('products/{id}', [ProductController::class, 'show'])->name('store-staff.product.show');
    
    // Create/edit products
    Route::get('products/create', [ProductController::class, 'create'])->name('store-staff.product.create');
    Route::post('products', [ProductController::class, 'store'])->name('store-staff.product.store');
    Route::get('products/{id}/edit', [ProductController::class, 'edit'])->name('store-staff.product.edit');
    Route::put('products/{id}', [ProductController::class, 'update'])->name('store-staff.product.update');
    Route::put('products/{id}/stock', [ProductController::class, 'updateStock'])->name('store-staff.product.update.stock');
    
    // Delete products
    Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('store-staff.product.destroy');
});
