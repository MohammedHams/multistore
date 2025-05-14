<?php

use App\Http\Controllers\Auth\StoreOwnerAuthController;
use App\Http\Controllers\Auth\StoreOwnerTwoFactorAuthController;
use App\Http\Controllers\StoreStaffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Store Owner Routes
|--------------------------------------------------------------------------
|
| Here is where you can register store owner routes for your application.
|
*/

// Store Owner Authentication Routes
Route::middleware('guest:store-owner')->group(function () {
    Route::get('/store-owner/login', [StoreOwnerAuthController::class, 'showLoginForm'])->name('store-owner.login');
    Route::post('/store-owner/login', [StoreOwnerAuthController::class, 'login'])->name('store-owner.login.submit');
});


Route::middleware(['auth:store-owner', 'store-owner.2fa', 'require-2fa-setup:store-owner'])->group(function () {
    Route::get('/store-owner/dashboard', function () {
        return view('store-owner.dashboard');
    })->name('store-owner.dashboard');

    // Staff Management Routes - Moved to Modules/Store/routes/web.php

    Route::post('/store-owner/logout', [StoreOwnerAuthController::class, 'logout'])->name('store-owner.logout');

    // Add your store owner protected routes here
    // All routes in this group are protected by 2FA
});
