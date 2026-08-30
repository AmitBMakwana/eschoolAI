<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
