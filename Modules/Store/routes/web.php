<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\StoreController;
use Modules\Store\Http\Controllers\StoreOwnerController;
use Modules\Store\Http\Controllers\StoreStaffController;
use Modules\Store\Http\Controllers\StoreOwnerStaffController;

/*
|--------------------------------------------------------------------------
| Store Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all routes for the Store module.
| These routes are loaded by the RouteServiceProvider within the Store module.
|
*/

// Admin routes - full access to all stores and management
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    // Store Management
    Route::get('stores', [\Modules\Store\Http\Controllers\StoreController::class, 'index'])->name('admin.store.index');
    Route::get('stores/create', [\Modules\Store\Http\Controllers\StoreController::class, 'create'])->name('admin.store.create');
    Route::post('stores', [\Modules\Store\Http\Controllers\StoreController::class, 'store'])->name('admin.store.store');
    Route::get('stores/{store}', [\Modules\Store\Http\Controllers\StoreController::class, 'show'])->name('admin.store.show');
    Route::get('stores/{store}/edit', [\Modules\Store\Http\Controllers\StoreController::class, 'edit'])->name('admin.store.edit');
    Route::put('stores/{store}', [\Modules\Store\Http\Controllers\StoreController::class, 'update'])->name('admin.store.update');
    Route::delete('stores/{store}', [\Modules\Store\Http\Controllers\StoreController::class, 'destroy'])->name('admin.store.destroy');

    // Store Owners Management
    Route::get('stores/{store}/owners', [\Modules\Store\Http\Controllers\StoreOwnerController::class, 'index'])->name('admin.store.owners.index');
    Route::get('stores/{store}/owners/create', [\Modules\Store\Http\Controllers\StoreOwnerController::class, 'create'])->name('admin.store.owners.create');
    Route::post('stores/{store}/owners', [\Modules\Store\Http\Controllers\StoreOwnerController::class, 'store'])->name('admin.store.owners.store');
    Route::delete('stores/{store}/owners/{user}', [\Modules\Store\Http\Controllers\StoreOwnerController::class, 'destroy'])->name('admin.store.owners.destroy');

    // Store Staff Management
    Route::get('stores/{store}/staff', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'index'])->name('admin.store.staff.index');
    Route::get('stores/{store}/staff/create', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'create'])->name('admin.store.staff.create');
    Route::post('stores/{store}/staff', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'store'])->name('admin.store.staff.store');
    Route::get('stores/{store}/staff/{user}/edit', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'edit'])->name('admin.store.staff.edit');
    Route::put('stores/{store}/staff/{user}', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'update'])->name('admin.store.staff.update');
    Route::delete('stores/{store}/staff/{user}', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'destroy'])->name('admin.store.staff.destroy');
    Route::get('stores/{store}/staff/{user}/permissions', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'permissions'])->name('admin.store.staff.permissions');
    Route::put('stores/{store}/staff/{user}/permissions', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'updatePermissions'])->name('admin.store.staff.permissions.update');
});

// Store Owner routes - access only to their own store
Route::middleware(['auth:store-owner'])->prefix('store-owner')->group(function () {
    // Store Management (limited to view only)
    Route::middleware(['\App\Http\Middleware\CheckPermission:view-store'])->group(function () {
        Route::get('stores', [\Modules\Store\Http\Controllers\StoreController::class, 'index'])->name('store-owner.store.index');
        Route::get('stores/{store}', [\Modules\Store\Http\Controllers\StoreController::class, 'show'])->name('store-owner.store.show');
    });
    
    // Store Management (edit)
    Route::middleware(['\App\Http\Middleware\CheckPermission:edit-store'])->group(function () {
        Route::get('stores/{store}/edit', [\Modules\Store\Http\Controllers\StoreController::class, 'edit'])->name('store-owner.store.edit');
        Route::put('stores/{store}', [\Modules\Store\Http\Controllers\StoreController::class, 'update'])->name('store-owner.store.update');
    });

    // Store Staff Management
    Route::middleware(['permission:manage-staff'])->group(function () {
        // Original store-specific staff routes
        Route::get('stores/{store}/staff', [\Modules\Store\Http\Controllers\StoreOwnerStaffController::class, 'index'])->name('store-owner.store.staff.index');
        Route::get('stores/{store}/staff/create', [\Modules\Store\Http\Controllers\StoreOwnerStaffController::class, 'create'])->name('store-owner.store.staff.create');
        Route::post('stores/{store}/staff', [\Modules\Store\Http\Controllers\StoreOwnerStaffController::class, 'store'])->name('store-owner.store.staff.store');
        Route::get('stores/{store}/staff/{user}/edit', [\Modules\Store\Http\Controllers\StoreOwnerStaffController::class, 'edit'])->name('store-owner.store.staff.edit');
        Route::put('stores/{store}/staff/{user}', [\Modules\Store\Http\Controllers\StoreOwnerStaffController::class, 'update'])->name('store-owner.store.staff.update');
        Route::delete('stores/{store}/staff/{user}', [\Modules\Store\Http\Controllers\StoreOwnerStaffController::class, 'destroy'])->name('store-owner.store.staff.destroy');
        
        // Refactored staff routes from main routes/store-owner.php
        Route::prefix('staff')->name('staff.')->group(function () {
            Route::get('/', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'index'])->name('index');
            Route::get('/create', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'create'])->name('create');
            Route::post('/', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'store'])->name('store');
            Route::get('/{id}', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'update'])->name('update');
            Route::delete('/{id}', [\Modules\Store\Http\Controllers\StoreStaffController::class, 'destroy'])->name('destroy');
        });
    });
});

// Store Staff routes - access based on permissions
Route::middleware(['auth:store-staff'])->prefix('store-staff')->group(function () {
    // Store Management (limited to view only)
    Route::middleware(['permission:view-store'])->group(function () {
        Route::get('stores', [\Modules\Store\Http\Controllers\StoreController::class, 'index'])->name('store-staff.store.index');
        Route::get('stores/{store}', [\Modules\Store\Http\Controllers\StoreController::class, 'show'])->name('store-staff.store.show');
    });
});

// Note: Duplicated store-staff routes have been removed
