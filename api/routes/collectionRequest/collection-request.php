<?php

use App\Http\Controllers\CollectionRequest\CollectionRequestController;
use Illuminate\Support\Facades\Route;

Route::controller(CollectionRequestController::class)->prefix('collection-requests')->group(function() {
    Route::post('', 'store');
    Route::get('mine', 'mine');
    Route::get('available', 'available');
    Route::get('{id}', 'details');
    Route::post('accept', 'accept');
    Route::post('collected', 'collected');
    Route::post('complete', 'complete');
    Route::post('cancel', 'cancel');
});
