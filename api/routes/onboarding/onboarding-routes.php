<?php

use App\Http\Controllers\Onboarding\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::prefix('onboarding')->controller(OnboardingController::class)->group(function() {
    Route::post('collector', 'onboardingCollector')->name('collector');
    Route::post('company', 'onboardingCompany')->name('company');

});
