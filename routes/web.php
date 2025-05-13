<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;

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
Route::get('two-factor-challenge', function() {
    return view('auth.two-factor-challenge');
})->middleware(['guest'])->name('two-factor.challenge');

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

// Protected routes
Route::middleware(['auth', 'verified', 'App\Http\Middleware\RequireTwoFactorAuth'])->group(function () {
    // User profile (accessible to all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Fallback route for authenticated users who are not admins
    Route::get('/access-denied', function () {
        return view('access-denied');
    })->name('access.denied');
});

// Admin protected routes
Route::middleware(['auth', 'verified', 'App\Http\Middleware\RequireTwoFactorAuth'])->group(function () {
    // Dashboard (only accessible to admins)
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    });
});
