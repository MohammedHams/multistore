<?php

use Illuminate\Support\Facades\Route;
// With the namespace prefix in RouteServiceProvider, we don't need to import the controller

// Admin routes - full access to all products
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::resource('products', 'ProductController')->names('admin.product');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('admin.product.update.stock');
});

// Store Owner routes - access only to their store's products
Route::middleware(['auth:store-owner'])->prefix('store-owner')->group(function () {
    Route::middleware(['permission:manage-products'])->group(function () {
        Route::resource('products', 'ProductController')->names('store-owner.product');
        Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('store-owner.product.update.stock');
    });
});

// Store Staff routes - access based on permissions
Route::middleware(['auth:store-staff'])->prefix('store-staff')->group(function () {
    // View products - requires view-products permission
    Route::get('products', ['ProductController', 'index'])->middleware(['permission:view-products'])->name('store-staff.product.index');
    Route::get('products/{id}', ['ProductController', 'show'])->middleware(['permission:view-products'])->name('store-staff.product.show');
    
    // Create/edit products - requires manage-products permission
    Route::get('products/create', ['ProductController', 'create'])->middleware(['permission:manage-products'])->name('store-staff.product.create');
    Route::post('products', ['ProductController', 'store'])->middleware(['permission:manage-products'])->name('store-staff.product.store');
    Route::get('products/{id}/edit', ['ProductController', 'edit'])->middleware(['permission:manage-products'])->name('store-staff.product.edit');
    Route::put('products/{id}', ['ProductController', 'update'])->middleware(['permission:manage-products'])->name('store-staff.product.update');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->middleware(['permission:manage-products'])->name('store-staff.product.update.stock');
    
    // Delete products - requires delete-products permission
    Route::delete('products/{id}', ['ProductController', 'destroy'])->middleware(['permission:delete-products'])->name('store-staff.product.destroy');
});
