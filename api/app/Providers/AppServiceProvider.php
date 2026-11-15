<?php

namespace App\Providers;

use App\Repositories\Eloquent\CompanyUser\CompanyUserEloquent;
use App\Repositories\Eloquent\Onboarding\OnboardingCollectorEloquent;
use App\Repositories\Eloquent\Onboarding\OnboardingCompanyEloquent;
use App\Repositories\Eloquent\UserEloquent;
use App\Repositories\Interface\CompanyUser\CompanyUserInterface;
use App\Repositories\Interface\Onboarding\OnboardingCollectorProfileInterface;
use App\Repositories\Interface\Onboarding\OnboardingCompanyInterface;
use App\Repositories\Interface\UserInterface;
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
            OnboardingCollectorEloquent::class
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
