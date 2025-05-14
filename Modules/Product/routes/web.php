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
    Route::resource('products', 'ProductController')->names('store-owner.product');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('store-owner.product.update.stock');
});

// Store Staff routes
Route::middleware(['auth:store-staff'])->prefix('store-staff')->group(function () {
    // View products
    Route::get('products', ['ProductController', 'index'])->name('store-staff.product.index');
    Route::get('products/{id}', ['ProductController', 'show'])->name('store-staff.product.show');
    
    // Create/edit products
    Route::get('products/create', ['ProductController', 'create'])->name('store-staff.product.create');
    Route::post('products', ['ProductController', 'store'])->name('store-staff.product.store');
    Route::get('products/{id}/edit', ['ProductController', 'edit'])->name('store-staff.product.edit');
    Route::put('products/{id}', ['ProductController', 'update'])->name('store-staff.product.update');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('store-staff.product.update.stock');
    
    // Delete products
    Route::delete('products/{id}', ['ProductController', 'destroy'])->name('store-staff.product.destroy');
});
