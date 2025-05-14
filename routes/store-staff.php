<?php

use App\Http\Controllers\Auth\StoreStaffAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Store Staff Routes
|--------------------------------------------------------------------------
|
| Here is where you can register store staff routes for your application.
|
*/

// Store Staff Authentication Routes
Route::middleware('guest:store-staff')->group(function () {
    Route::get('/store-staff/login', [StoreStaffAuthController::class, 'showLoginForm'])->name('store-staff.login');
    Route::post('/store-staff/login', [StoreStaffAuthController::class, 'login'])->name('store-staff.login.submit');
});

Route::middleware(['auth:store-staff'])->group(function () {
    Route::get('/store-staff/dashboard', function () {
        return view('store-staff.dashboard');
    })->name('store-staff.dashboard');
    
    Route::post('/store-staff/logout', [StoreStaffAuthController::class, 'logout'])->name('store-staff.logout');
    
    // Add your store staff protected routes here
});
