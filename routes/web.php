<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/swagger', '/docs');
Route::redirect('/api/documentation', '/docs');
