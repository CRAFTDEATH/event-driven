<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/{id}', function ($id) {
    $order = \App\Models\OrderEvent::where('order_id',$id)->get();
    return Inertia::render('Show', ['order' => $order]);
});