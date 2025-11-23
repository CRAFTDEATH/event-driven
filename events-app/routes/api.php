<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\OrderController;
use App\Http\Controllers\v1\OrderEventController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::middleware(["auth:sanctum"])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::prefix('/v1')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::patch('/orders/{id}', [OrderController::class, 'updateDetails']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}/status', [OrderEventController::class, 'status']);
        Route::patch('/orders/{id}/status', [OrderController::class, 'update']);
        Route::get('/orders/{id}/events', [OrderEventController::class, 'show']);
    });
});
