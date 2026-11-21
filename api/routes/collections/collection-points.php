<?php

use App\Http\Controllers\CollectionPoint\CollectionPointController;
use Illuminate\Support\Facades\Route;

Route::controller(CollectionPointController::class)->prefix('collection-points')->group(function() {
    Route::get('', 'all');
    Route::get('for-provider', 'allForProvider');
    Route::post('', 'store');
    Route::get('{id}', 'show')->whereNumber('id');
    Route::put('{id}', 'update')->whereNumber('id');
    Route::patch('{id}', 'update')->whereNumber('id');
    Route::delete('{id}', 'destroy')->whereNumber('id');
    Route::post('{id}/recovery', 'recovery')->whereNumber('id');
});
