<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/documentation', function () {
    return file_get_contents(public_path('api-documentation.html'));
});

Route::get('/docs', function () {
    return redirect('/documentation');
});
