<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->prefix('auth')->group(function() {
    Route::post('login', 'login')->middleware('throttle:5,1')->name('login');
    Route::post('register', 'register')->middleware('throttle:3,1')->name('register');

    Route::middleware(['auth:sanctum'])->group(function() {
        Route::post('logout', 'logout')->name('logout');
        Route::get('me', 'me')->name('me');
    });
});
