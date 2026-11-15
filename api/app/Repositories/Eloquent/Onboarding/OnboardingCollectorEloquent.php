<?php

namespace App\Repositories\Eloquent\Onboarding;

use App\Models\CollectorProfile;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Onboarding\OnboardingCollectorProfileInterface;

class OnboardingCollectorEloquent extends BaseEloquent implements OnboardingCollectorProfileInterface
{
    protected function modelClass(): string
    {
        return CollectorProfile::class;
    }
}
