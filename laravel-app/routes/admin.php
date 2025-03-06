<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Welcome to Admin Dashboard';
})->name('dashboard');
