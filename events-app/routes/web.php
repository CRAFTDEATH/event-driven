<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return view('welcome');
});

// Simple Inertia pages for the frontend
Route::get('/login', function () {
    return Inertia::render('Auth/Login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
});

Route::get('/entries', function () {
    return Inertia::render('Entries/Index');
});

Route::get('/entries/create', function () {
    return Inertia::render('Entries/Create');
});

Route::get('/entries/{id}', function ($id) {
    return Inertia::render('Entries/Show', ['id' => $id]);
});
