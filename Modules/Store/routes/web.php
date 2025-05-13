<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\StoreController;
use Modules\Store\Http\Controllers\StoreOwnerController;
use Modules\Store\Http\Controllers\StoreStaffController;

// Define the controllers once to avoid duplicate class loading
$storeController = StoreController::class;
$storeOwnerController = StoreOwnerController::class;
$storeStaffController = StoreStaffController::class;

// Routes with original names but protected by admin guard
Route::middleware(['auth', 'verified'])->group(function () use ($storeController, $storeOwnerController, $storeStaffController) {
    // Store Management
    Route::resource('stores', $storeController)->names('store');

    // Store Owners Management
    Route::get('stores/{store}/owners', [$storeOwnerController, 'index'])->name('store.owners.index');
    Route::get('stores/{store}/owners/create', [$storeOwnerController, 'create'])->name('store.owners.create');
    Route::post('stores/{store}/owners', [$storeOwnerController, 'store'])->name('store.owners.store');
    Route::delete('stores/{store}/owners/{user}', [$storeOwnerController, 'destroy'])->name('store.owners.destroy');

    // Store Staff Management
    Route::get('stores/{store}/staff', [$storeStaffController, 'index'])->name('store.staff.index');
    Route::get('stores/{store}/staff/create', [$storeStaffController, 'create'])->name('store.staff.create');
    Route::post('stores/{store}/staff', [$storeStaffController, 'store'])->name('store.staff.store');
    Route::get('stores/{store}/staff/{user}/edit', [$storeStaffController, 'edit'])->name('store.staff.edit');
    Route::put('stores/{store}/staff/{user}', [$storeStaffController, 'update'])->name('store.staff.update');
    Route::delete('stores/{store}/staff/{user}', [$storeStaffController, 'destroy'])->name('store.staff.destroy');
});

// Admin routes - full access to all stores and management
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () use ($storeController, $storeOwnerController, $storeStaffController) {
    // Store Management
    Route::resource('stores', $storeController)->names('admin.store');

    // Store Owners Management
    Route::get('stores/{store}/owners', [$storeOwnerController, 'index'])->name('admin.store.owners.index');
    Route::get('stores/{store}/owners/create', [$storeOwnerController, 'create'])->name('admin.store.owners.create');
    Route::post('stores/{store}/owners', [$storeOwnerController, 'store'])->name('admin.store.owners.store');
    Route::delete('stores/{store}/owners/{user}', [$storeOwnerController, 'destroy'])->name('admin.store.owners.destroy');

    // Store Staff Management
    Route::get('stores/{store}/staff', [$storeStaffController, 'index'])->name('admin.store.staff.index');
    Route::get('stores/{store}/staff/create', [$storeStaffController, 'create'])->name('admin.store.staff.create');
    Route::post('stores/{store}/staff', [$storeStaffController, 'store'])->name('admin.store.staff.store');
    Route::get('stores/{store}/staff/{user}/edit', [$storeStaffController, 'edit'])->name('admin.store.staff.edit');
    Route::put('stores/{store}/staff/{user}', [$storeStaffController, 'update'])->name('admin.store.staff.update');
    Route::delete('stores/{store}/staff/{user}', [$storeStaffController, 'destroy'])->name('admin.store.staff.destroy');
});

// Store Owner routes - access only to their own store
Route::middleware(['auth', 'verified'])->prefix('store-owner')->group(function () use ($storeController, $storeStaffController) {
    // Store Management (limited to view only)
    Route::get('stores', [$storeController, 'index'])->name('store-owner.store.index');
    Route::get('stores/{store}', [$storeController, 'show'])->name('store-owner.store.show');
    Route::get('stores/{store}/edit', [$storeController, 'edit'])->name('store-owner.store.edit');
    Route::put('stores/{store}', [$storeController, 'update'])->name('store-owner.store.update');

    // Store Staff Management
    Route::get('stores/{store}/staff', [$storeStaffController, 'index'])->name('store-owner.store.staff.index');
    Route::get('stores/{store}/staff/create', [$storeStaffController, 'create'])->name('store-owner.store.staff.create');
    Route::post('stores/{store}/staff', [$storeStaffController, 'store'])->name('store-owner.store.staff.store');
    Route::get('stores/{store}/staff/{user}/edit', [$storeStaffController, 'edit'])->name('store-owner.store.staff.edit');
    Route::put('stores/{store}/staff/{user}', [$storeStaffController, 'update'])->name('store-owner.store.staff.update');
    Route::delete('stores/{store}/staff/{user}', [$storeStaffController, 'destroy'])->name('store-owner.store.staff.destroy');
});

// Store Staff routes - access based on permissions
Route::middleware(['auth', 'verified'])->prefix('store-staff')->group(function () use ($storeController) {
    // Store Management (limited to view only)
    Route::get('stores', [$storeController, 'index'])->name('store-staff.store.index');
    Route::get('stores/{store}', [$storeController, 'show'])->name('store-staff.store.show');
});
