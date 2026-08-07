<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\KarigarController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('ping', fn () => response()->json([
        'success' => true,
        'message' => 'pong',
        'data' => null,
    ]));

    Route::post('auth/login-otp', [AuthController::class, 'sendOtp'])
        ->middleware('throttle:5,1');
    Route::post('auth/verify-otp', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::post('workshops', [WorkshopController::class, 'store']);

        Route::middleware('tenant.scope')->group(function () {
            Route::get('dashboard/summary', [DashboardController::class, 'summary']);

            Route::get('orders', [OrderController::class, 'index']);
            Route::post('orders', [OrderController::class, 'store']);
            Route::get('orders/{order}', [OrderController::class, 'show']);
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
            Route::patch('orders/{order}/karigar', [OrderController::class, 'assignKarigar']);
            Route::patch('orders/{order}/costing', [OrderController::class, 'updateCosting']);
            Route::post('orders/{order}/stages', [OrderController::class, 'addStage']);
            Route::patch('orders/{order}/stages/{stage}', [OrderController::class, 'updateStage']);
            Route::delete('orders/{order}/stages/{stage}', [OrderController::class, 'deleteStage']);

            Route::get('karigars', [KarigarController::class, 'index']);
            Route::post('karigars', [KarigarController::class, 'store']);
            Route::get('karigars/{karigar}', [KarigarController::class, 'show']);
            Route::post('karigars/{karigar}/advances', [KarigarController::class, 'giveAdvance']);
            Route::post('karigars/{karigar}/settle-weekly', [KarigarController::class, 'settleWeekly']);

            Route::get('customers', [CustomerController::class, 'index']);
            Route::get('customers/search', [CustomerController::class, 'search']);

            Route::post('payments/receive', [FinanceController::class, 'receivePayment']);
            Route::get('reports/summary', [FinanceController::class, 'reportSummary']);
        });
    });
});
