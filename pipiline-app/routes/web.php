<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
});

// Orders UI (handled by controller using Inertia)
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/search', [OrderController::class, 'search'])->name('orders.search');
Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');