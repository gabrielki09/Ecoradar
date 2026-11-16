<?php

use Illuminate\Support\Facades\Route;

Route::prefix('general')->group(function() {
    Route::get('cities', [\App\Http\Controllers\General\CityController::class, 'all']);
});
