<?php

use App\Models\OrderEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;
use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\OrderEventController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(["auth:sanctum"])->group(function () {
    Route::prefix('/v1')->group(function () {
        Route::post('/orders', [OrderEventController::class, 'store']);
        Route::get('/orders/{id}/events', [OrderEventController::class, 'show']);
    });
});
