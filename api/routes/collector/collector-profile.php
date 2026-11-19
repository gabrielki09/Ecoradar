<?php

use App\Http\Controllers\Collector\CollectorProfileController;
use Illuminate\Support\Facades\Route;

Route::controller(CollectorProfileController::class)->prefix('/collector-profile')->group(function() {
    Route::put('', 'update');
    Route::get('', 'show');
});
