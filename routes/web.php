<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/showcase', function () {
    return view('showcase');
});

Route::get('/design-system', function () {
    return view('showcase');
});
