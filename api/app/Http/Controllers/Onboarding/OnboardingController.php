<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collector\CollectorProfileStoreRequest;
use App\Http\Requests\Onboarding\OnboardingCompanyStoreRequest;
use App\Services\Onboarding\OnboardingCollectorService;
use App\Services\Onboarding\OnboardingCompanyService;

class OnboardingController extends Controller
{
    public function __construct(
        protected OnboardingCompanyService $onboardingCompanyService,
        protected OnboardingCollectorService $onboardingCollectorService
    ){}

    public function onboardingCollector(CollectorProfileStoreRequest $req)
    {
        return apiSuccess(
            'Coletor cadastrada com sucesso!',
            [
                'collector_profile' => $this->onboardingCollectorService->store(
                    data: $req->validated(),
                    user: $req->user()
                )
            ],
            201
        );
    }

    public function onboardingCompany(OnboardingCompanyStoreRequest $req)
    {
        return apiSuccess(
            'Empresa cadastrada com sucesso!',
            [
                'company' => $this->onboardingCompanyService->store(
                    $req->user(),
                    $req->validated()
                )
            ],
            201
        );
    }
}
