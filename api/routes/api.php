<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    require_once __DIR__."/auth/auth-routes.php";

    Route::middleware(['auth:sanctum'])->group(function() {
        require_once __DIR__."/onboarding/onboarding-routes.php";

    });
});
