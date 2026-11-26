<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function() {
    require __DIR__."/auth/auth-routes.php";
    require __DIR__."/general/general-routes.php";

    Route::get('collection-points/public', [\App\Http\Controllers\CollectionPoint\PublicCollectionPointController::class, 'index']);

    Route::middleware(['auth:sanctum'])->group(function() {
        Route::get('reward-points', [\App\Http\Controllers\CollectionRequest\RewardPointController::class, 'show']);
        Route::get('reward-points/history', [\App\Http\Controllers\CollectionRequest\RewardPointController::class, 'history']);
        require __DIR__."/onboarding/onboarding-routes.php";
        require __DIR__."/wasteCategories/waste-categories.php";

        Route::get('company-profile/{companyId}', [\App\Http\Controllers\Company\CompanyProfileController::class, 'show'])->whereNumber('companyId');
        Route::put('company-profile/{companyId}', [\App\Http\Controllers\Company\CompanyProfileController::class, 'update'])->whereNumber('companyId');

        require __DIR__."/collector/collector-profile.php";
        require __DIR__."/collections/collection-request.php";
        require __DIR__."/collections/collection-points.php";
    });
});
