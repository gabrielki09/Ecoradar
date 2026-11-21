<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    require_once __DIR__."/auth/auth-routes.php";
    require_once __DIR__."/general/general-routes.php";

    Route::middleware(['auth:sanctum'])->group(function() {
        require_once __DIR__."/onboarding/onboarding-routes.php";
        require_once __DIR__."/wasteCategories/waste-categories.php";

        require_once __DIR__."/collector/collector-profile.php";
        require_once __DIR__."/collections/collection-request.php";
        require_once __DIR__."/collections/collection-points.php";
    });
});
