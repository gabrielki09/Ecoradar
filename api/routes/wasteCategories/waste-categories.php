<?php

use App\Http\Controllers\WasteCategoryController;
use Illuminate\Support\Facades\Route;


Route::apiResource('waste-categories', WasteCategoryController::class);
