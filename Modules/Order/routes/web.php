<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;

// Define the controller once to avoid duplicate class loading
$orderController = OrderController::class;

// Admin routes - full access to all orders
Route::middleware(['auth:admin'])->prefix('admin')->group(function () use ($orderController) {
    Route::prefix('orders')->group(function () use ($orderController) {
        Route::get('/', [$orderController, 'index'])->name('admin.order.index');
        Route::get('/create', [$orderController, 'create'])->name('admin.order.create');
        Route::post('/', [$orderController, 'store'])->name('admin.order.store');
        Route::get('/{id}', [$orderController, 'show'])->name('admin.order.show');
        Route::get('/{id}/edit', [$orderController, 'edit'])->name('admin.order.edit');
        Route::put('/{id}', [$orderController, 'update'])->name('admin.order.update');
        Route::delete('/{id}', [$orderController, 'destroy'])->name('admin.order.destroy');

        // Additional routes
        Route::put('/{id}/status', [$orderController, 'updateStatus'])->name('admin.order.update.status');
        Route::put('/{id}/payment-status', [$orderController, 'updatePaymentStatus'])->name('admin.order.update.payment-status');
    });
});

// Store Owner routes - access only to their store's orders
Route::middleware(['auth:store-owner'])->prefix('store-owner')->group(function () use ($orderController) {
    Route::prefix('orders')->middleware(['permission:manage-orders'])->group(function () use ($orderController) {
        Route::get('/', [$orderController, 'index'])->name('store-owner.order.index');
        Route::get('/create', [$orderController, 'create'])->name('store-owner.order.create');
        Route::post('/', [$orderController, 'store'])->name('store-owner.order.store');
        Route::get('/{id}', [$orderController, 'show'])->name('store-owner.order.show');
        Route::get('/{id}/edit', [$orderController, 'edit'])->name('store-owner.order.edit');
        Route::put('/{id}', [$orderController, 'update'])->name('store-owner.order.update');
        Route::delete('/{id}', [$orderController, 'destroy'])->name('store-owner.order.destroy');

        // Additional routes
        Route::put('/{id}/status', [$orderController, 'updateStatus'])->name('store-owner.order.update.status');
        Route::put('/{id}/payment-status', [$orderController, 'updatePaymentStatus'])->name('store-owner.order.update.payment-status');
    });
});

// Store Staff routes - access based on permissions
Route::middleware(['auth:store-staff'])->prefix('store-staff')->group(function () use ($orderController) {
    // View orders - requires view-orders permission
    Route::prefix('orders')->group(function () use ($orderController) {
        Route::get('/', [$orderController, 'index'])->middleware(['permission:view-orders'])->name('store-staff.order.index');
        Route::get('/{id}', [$orderController, 'show'])->middleware(['permission:view-orders'])->name('store-staff.order.show');
        
        // Create/edit orders - requires manage-orders permission
        Route::get('/create', [$orderController, 'create'])->middleware(['permission:manage-orders'])->name('store-staff.order.create');
        Route::post('/', [$orderController, 'store'])->middleware(['permission:manage-orders'])->name('store-staff.order.store');
        Route::get('/{id}/edit', [$orderController, 'edit'])->middleware(['permission:manage-orders'])->name('store-staff.order.edit');
        Route::put('/{id}', [$orderController, 'update'])->middleware(['permission:manage-orders'])->name('store-staff.order.update');
        
        // Delete orders - requires delete-orders permission
        Route::delete('/{id}', [$orderController, 'destroy'])->middleware(['permission:delete-orders'])->name('store-staff.order.destroy');

        // Additional routes - requires manage-orders permission
        Route::put('/{id}/status', [$orderController, 'updateStatus'])->middleware(['permission:manage-orders'])->name('store-staff.order.update.status');
        Route::put('/{id}/payment-status', [$orderController, 'updatePaymentStatus'])->middleware(['permission:manage-orders'])->name('store-staff.order.update.payment-status');
    });
});

Route::prefix('stores/{storeId}/orders')->group(function () use ($orderController) {
    Route::get('/', [$orderController, 'indexByStore'])->name('store.order.index');
    Route::get('/create', [$orderController, 'createForStore'])->name('store.order.create');
});
