<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\StoreOwnerAuthController;
use App\Http\Controllers\Auth\StoreStaffAuthController;
use App\Http\Controllers\Auth\StoreOwnerTwoFactorAuthController;

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


// Multi-guard authentication routes
// Admin Authentication Routes
Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
});

// Two-Factor Authentication Routes for all user types
Route::group(['middleware' => ['web']], function () {
    // Common two-factor challenge routes
    Route::get('/two-factor-challenge', [\App\Http\Controllers\Auth\TwoFactorAuthController::class, 'showChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [\App\Http\Controllers\Auth\TwoFactorAuthController::class, 'challenge'])->name('two-factor.challenge.submit');
    Route::post('/two-factor-challenge/resend', [\App\Http\Controllers\Auth\TwoFactorAuthController::class, 'sendCode'])->name('two-factor.resend');
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

// Access denied route
Route::get('/access-denied', function () {
    return view('errors.access-denied');
})->name('access.denied');

// User Two-Factor Authentication Routes

// Redirect root to appropriate dashboard based on authentication
Route::middleware(['web'])->get('/', function () {
    if (auth()->guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    } elseif (auth()->guard('store-owner')->check()) {
        return redirect()->route('store-owner.dashboard');
    } elseif (auth()->guard('store-staff')->check()) {
        return redirect()->route('store-staff.dashboard');
    } else {
        return redirect()->route('admin.login');
    }
});

// Admin protected routes
Route::middleware(['web', 'auth:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Ensure Product module routes are loaded
});

// Store Owner Two-Factor Challenge Routes (accessible without being fully authenticated)
Route::middleware(['web'])->group(function () {
    
    Route::get('/store-owner/two-factor-challenge', [StoreOwnerAuthController::class, 'showTwoFactorChallenge'])->name('store-owner.two-factor.challenge');
    Route::post('/store-owner/two-factor-challenge', [StoreOwnerAuthController::class, 'twoFactorChallenge'])->name('store-owner.two-factor.challenge.submit');
    Route::post('/store-owner/two-factor-challenge/resend', [StoreOwnerAuthController::class, 'resendTwoFactorCode'])->name('store-owner.two-factor.resend');
});

// Protected Store Owner Routes
Route::middleware(['auth:store-owner'])->group(function () {
    Route::get('/store-owner/dashboard', function () {
        return view('store-owner.dashboard');
    })->name('store-owner.dashboard');

    Route::post('/store-owner/logout', [StoreOwnerAuthController::class, 'logout'])->name('store-owner.logout');
});

// Store Staff protected routes
Route::middleware(['auth:store-staff'])->group(function () {
    Route::get('/store-staff/dashboard', function () {
        return view('store-staff.dashboard');
    })->name('store-staff.dashboard');

    Route::post('/store-staff/logout', [StoreStaffAuthController::class, 'logout'])->name('store-staff.logout');

    // Add your store staff protected routes here
});
