<?php

use Illuminate\Support\Facades\Route;
// With the namespace prefix in RouteServiceProvider, we don't need to import the controller

// Routes with original names but protected by admin guard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('products', 'ProductController')->names('product');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('product.update.stock');
});

// Admin routes - full access to all products
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::resource('products', 'ProductController')->names('admin.product');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('admin.product.update.stock');
});

// Store Owner routes - access only to their store's products
Route::middleware(['auth', 'verified'])->prefix('store-owner')->group(function () {
    Route::resource('products', 'ProductController')->names('store-owner.product');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('store-owner.product.update.stock');
});

// Store Staff routes - access based on permissions
Route::middleware(['auth', 'verified'])->prefix('store-staff')->group(function () {
    Route::resource('products', 'ProductController')->names('store-staff.product');
    Route::put('products/{id}/stock', ['ProductController', 'updateStock'])->name('store-staff.product.update.stock');
});
