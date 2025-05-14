<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\StoreOwnerAuthController;
use App\Http\Controllers\Auth\StoreStaffAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
// Language Switcher
Route::get('language/{locale}', [LanguageController::class, 'switchLang'])->name('language.switch');

// Two-factor authentication email OTP routes
Route::get('two-factor/send-code', [\App\Http\Controllers\Auth\TwoFactorAuthController::class, 'sendCode'])->name('two-factor.send-code');
Route::post('two-factor/verify', [\App\Http\Controllers\Auth\TwoFactorAuthController::class, 'verify'])->name('two-factor.verify');
Route::get('two-factor/login', function() {
    return redirect()->route('two-factor.challenge');
})->middleware(['guest'])->name('two-factor.login.get');
Route::post('two-factor/login', [\App\Http\Controllers\Auth\TwoFactorAuthController::class, 'verify'])->name('two-factor.login');
Route::get('two-factor/resend', [\App\Http\Controllers\Auth\TwoFactorAuthController::class, 'sendCode'])->name('two-factor.resend');
Route::get('two-factor-challenge', function() {
    return view('auth.two-factor-challenge');
})->middleware(['guest'])->name('two-factor.challenge');

// Multi-guard authentication routes
// Admin Authentication Routes
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
});

// Store Owner Authentication Routes
Route::middleware('guest:store-owner')->group(function () {
    Route::get('/store-owner/login', [StoreOwnerAuthController::class, 'showLoginForm'])->name('store-owner.login');
    Route::post('/store-owner/login', [StoreOwnerAuthController::class, 'login'])->name('store-owner.login.submit');
});

// Store Staff Authentication Routes
Route::middleware('guest:store-staff')->group(function () {
    Route::get('/store-staff/login', [StoreStaffAuthController::class, 'showLoginForm'])->name('store-staff.login');
    Route::post('/store-staff/login', [StoreStaffAuthController::class, 'login'])->name('store-staff.login.submit');
});

// Authentication routes (handled by Fortify)
// Laravel Fortify automatically registers these routes:
// - /login (GET & POST)
// - /logout (POST)
// - /register (GET & POST)
// - /forgot-password (GET & POST)
// - /reset-password (GET & POST)
// - /email/verify (GET & POST)
// - /email/verification-notification (POST)
// - /user/profile-information (PUT)
// - /user/password (PUT)
// - /user/two-factor-authentication (POST, DELETE)
// - /user/confirmed-two-factor-authentication (POST)
// - /user/two-factor-recovery-codes (POST)

// Common routes
Route::get('/access-denied', function () {
    return view('access-denied');
})->name('access.denied');

// Redirect root to appropriate dashboard based on authentication
Route::get('/', function () {
    if (auth()->guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    } elseif (auth()->guard('store-owner')->check()) {
        return redirect()->route('store-owner.dashboard');
    } elseif (auth()->guard('store-staff')->check()) {
        return redirect()->route('store-staff.dashboard');
    } else {
        return redirect()->route('login');
    }
});

// Admin protected routes
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    
    // Add your admin protected routes here
});

// Store Owner protected routes
Route::middleware(['auth:store-owner'])->group(function () {
    Route::get('/store-owner/dashboard', function () {
        return view('store-owner.dashboard');
    })->name('store-owner.dashboard');
    
    Route::post('/store-owner/logout', [StoreOwnerAuthController::class, 'logout'])->name('store-owner.logout');
    
    // Add your store owner protected routes here
});

// Store Staff protected routes
Route::middleware(['auth:store-staff'])->group(function () {
    Route::get('/store-staff/dashboard', function () {
        return view('store-staff.dashboard');
    })->name('store-staff.dashboard');
    
    Route::post('/store-staff/logout', [StoreStaffAuthController::class, 'logout'])->name('store-staff.logout');
    
    // Add your store staff protected routes here
});
