<?php

use App\Http\Controllers\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/login', function () {
    return view('login');
});

Route::get('/', function () {
    $plans = \App\Models\Plan::where('is_active', true)->orderBy('price_monthly')->get();
    return view('landing', compact('plans'));
});

Route::get('/landing', function () {
    $plans = \App\Models\Plan::where('is_active', true)->orderBy('price_monthly')->get();
    return view('landing', compact('plans'));
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

Route::get('/docs/download', function () {
    $filePath = public_path('docs/eschoolAI_Master_Documentation.doc');
    if (!file_exists($filePath)) {
        $filePath = base_path('eschoolAI_Master_Documentation.doc');
    }
    return response()->download($filePath, 'eschoolAI_Master_Documentation.doc', [
        'Content-Type' => 'application/msword',
    ]);
});

Route::get('/documentation.doc', function () {
    $filePath = public_path('docs/eschoolAI_Master_Documentation.doc');
    if (!file_exists($filePath)) {
        $filePath = base_path('eschoolAI_Master_Documentation.doc');
    }
    return response()->download($filePath, 'eschoolAI_Master_Documentation.doc', [
        'Content-Type' => 'application/msword',
    ]);
});

Route::get('/showcase', function () {
    return view('showcase');
});

Route::get('/design-system', function () {
    return view('showcase');
});
