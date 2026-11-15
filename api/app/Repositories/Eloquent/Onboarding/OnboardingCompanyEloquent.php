<?php

namespace App\Repositories\Eloquent\Onboarding;

use App\Models\Company\Company;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Onboarding\OnboardingCompanyInterface;

class OnboardingCompanyEloquent extends BaseEloquent implements OnboardingCompanyInterface
{
    protected function modelClass(): string
    {
        return Company::class;
    }
}
