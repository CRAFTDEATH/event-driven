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
Route::get('/v1/orders/{id}/events/stream', function ($id, Request $request) {
    $token = $request->query('token');
    if (!$token) abort(401);

    $parts = explode('|', $token);
    $accessToken = PersonalAccessToken::findToken($parts[1]);
    if (!$accessToken) abort(401);

    $user = $accessToken->tokenable;
    Auth::login($user);

    return response()->stream(function () use ($id) {
        while (true) {
            $events = \App\Models\OrderEvent::where('order_id', $id)->get();
            echo "data: " . json_encode($events) . "\n\n";
            ob_flush();
            flush();
            sleep(10);
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    ]);
});
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(["auth:sanctum"])->group(function () {
    Route::prefix('/v1')->group(function () {
        Route::post('/orders', [OrderEventController::class, 'store']);
        Route::get('/orders/{id}/events', [OrderEventController::class, 'show']);
    });
});
