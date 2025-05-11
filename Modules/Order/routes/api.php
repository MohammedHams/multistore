<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Order\app\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
        
        // Additional routes
        Route::put('/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('/{id}/payment-status', [OrderController::class, 'updatePaymentStatus']);
        Route::get('/search', [OrderController::class, 'search']);
    });
    
    Route::prefix('stores/{storeId}/orders')->group(function () {
        Route::get('/', [OrderController::class, 'indexByStore']);
    });
});
