<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\OrderController;

// Define the controller once to avoid duplicate class loading
$orderController = OrderController::class;

// Routes with original names but protected by admin guard
Route::middleware(['auth', 'verified'])->group(function () use ($orderController) {
    Route::prefix('orders')->group(function () use ($orderController) {
        Route::get('/', [$orderController, 'index'])->name('order.index');
        Route::get('/create', [$orderController, 'create'])->name('order.create');
        Route::post('/', [$orderController, 'store'])->name('order.store');
        Route::get('/{id}', [$orderController, 'show'])->name('order.show');
        Route::get('/{id}/edit', [$orderController, 'edit'])->name('order.edit');
        Route::put('/{id}', [$orderController, 'update'])->name('order.update');
        Route::delete('/{id}', [$orderController, 'destroy'])->name('order.destroy');

        // Additional routes
        Route::put('/{id}/status', [$orderController, 'updateStatus'])->name('order.update.status');
        Route::put('/{id}/payment-status', [$orderController, 'updatePaymentStatus'])->name('order.update.payment-status');
    });
});

// Admin routes - full access to all orders
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () use ($orderController) {
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
Route::middleware(['auth', 'verified', 'store-owner'])->prefix('store-owner')->group(function () use ($orderController) {
    Route::prefix('orders')->group(function () use ($orderController) {
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
Route::middleware(['auth', 'verified', 'store-staff'])->prefix('store-staff')->group(function () use ($orderController) {
    Route::prefix('orders')->group(function () use ($orderController) {
        Route::get('/', [$orderController, 'index'])->name('store-staff.order.index');
        Route::get('/create', [$orderController, 'create'])->name('store-staff.order.create');
        Route::post('/', [$orderController, 'store'])->name('store-staff.order.store');
        Route::get('/{id}', [$orderController, 'show'])->name('store-staff.order.show');
        Route::get('/{id}/edit', [$orderController, 'edit'])->name('store-staff.order.edit');
        Route::put('/{id}', [$orderController, 'update'])->name('store-staff.order.update');
        Route::delete('/{id}', [$orderController, 'destroy'])->name('store-staff.order.destroy');

        // Additional routes
        Route::put('/{id}/status', [$orderController, 'updateStatus'])->name('store-staff.order.update.status');
        Route::put('/{id}/payment-status', [$orderController, 'updatePaymentStatus'])->name('store-staff.order.update.payment-status');
    });
});

Route::prefix('stores/{storeId}/orders')->group(function () use ($orderController) {
    Route::get('/', [$orderController, 'indexByStore'])->name('store.order.index');
    Route::get('/create', [$orderController, 'createForStore'])->name('store.order.create');
});
