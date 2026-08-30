<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
});

Route::get('/', function () {
    return view('app');
});

Route::get('/app', function () {
    return view('app');
});

Route::get('/portal', function () {
    return view('app');
});

Route::get('/dashboard', function () {
    return view('app');
});

Route::get('/healthz', [HealthCheckController::class, 'check']);

Route::get('/docs', function () {
    return view('docs');
});

Route::get('/showcase', function () {
    return view('showcase');
});

Route::get('/design-system', function () {
    return view('showcase');
});
