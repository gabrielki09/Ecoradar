<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Onboarding\OnboardingCollectorStoreRequest;
use App\Http\Requests\Onboarding\OnboardingCompanyStoreRequest;
use App\Services\Onboarding\OnboardingCollectorService;
use App\Services\Onboarding\OnboardingCompanyService;

class OnboardingController extends Controller
{
    public function __construct(
        protected OnboardingCompanyService $onboardingCompanyService,
        protected OnboardingCollectorService $onboardingCollectorService
    ){}

    public function onboardingCollector(OnboardingCollectorStoreRequest $req)
    {
        return apiSuccess(
            'Coletor cadastrada com sucesso!',
            [
                'collector_profile' => $this->onboardingCollectorService->store($req->user())
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
