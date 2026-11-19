<?php

namespace App\Providers;

use App\Repositories\Eloquent\Collection\CollectionHistoryEloquent;
use App\Repositories\Eloquent\Collection\CollectionRequestEloquent;
use App\Repositories\Eloquent\Collection\CollectionRequestItemEloquent;
use App\Repositories\Eloquent\Collector\CollectorProfileCityEloquent;
use App\Repositories\Eloquent\Collector\CollectorProfileWasteCategoryEloquent;
use App\Repositories\Eloquent\CompanyUser\CompanyUserEloquent;
use App\Repositories\Eloquent\General\CityEloquent;
use App\Repositories\Eloquent\Onboarding\OnboardingCollectorProfileEloquent;
use App\Repositories\Eloquent\Onboarding\OnboardingCompanyEloquent;
use App\Repositories\Eloquent\UserEloquent;
use App\Repositories\Eloquent\WasteCategoryEloquent;
use App\Repositories\Interface\Collector\CollectorProfileCityInterface;
use App\Repositories\Interface\Collection\CollectionHistoryInterface;
use App\Repositories\Interface\Collection\CollectionRequestInterface;
use App\Repositories\Interface\Collection\CollectionRequestItemInterface;
use App\Repositories\Interface\Collector\CollectorProfileWasteCategoryInterface;
use App\Repositories\Interface\CompanyUser\CompanyUserInterface;
use App\Repositories\Interface\General\CityInterface;
use App\Repositories\Interface\Onboarding\OnboardingCollectorProfileInterface;
use App\Repositories\Interface\Onboarding\OnboardingCompanyInterface;
use App\Repositories\Interface\UserInterface;
use App\Repositories\Interface\WasteCategoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserInterface::class,
            UserEloquent::class
        );

        $this->app->bind(
            OnboardingCompanyInterface::class,
            OnboardingCompanyEloquent::class
        );

        $this->app->bind(
            CompanyUserInterface::class,
            CompanyUserEloquent::class
        );

        $this->app->bind(
            OnboardingCollectorProfileInterface::class,
            OnboardingCollectorProfileEloquent::class
        );

        $this->app->bind(
            CollectorProfileWasteCategoryInterface::class,
            CollectorProfileWasteCategoryEloquent::class
        );

        $this->app->bind(
            CollectorProfileCityInterface::class,
            CollectorProfileCityEloquent::class
        );

        $this->app->bind(
            CollectionRequestInterface::class,
            CollectionRequestEloquent::class
        );

        $this->app->bind(
            CollectionRequestItemInterface::class,
            CollectionRequestItemEloquent::class
        );

        $this->app->bind(
            CollectionHistoryInterface::class,
            CollectionHistoryEloquent::class
        );

        $this->app->bind(
            WasteCategoryInterface::class,
            WasteCategoryEloquent::class,
        );

        $this->app->bind(
            CityInterface::class,
            CityEloquent::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
