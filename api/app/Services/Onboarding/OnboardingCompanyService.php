<?php

namespace App\Services\Onboarding;

use App\Enum\ProfileTypes;
use App\Models\Company\Company;
use App\Models\User;
use App\Repositories\Interface\CompanyUser\CompanyUserInterface;
use App\Repositories\Interface\Onboarding\OnboardingCompanyInterface;
use App\Repositories\Interface\UserInterface;
use Illuminate\Support\Facades\DB;

class OnboardingCompanyService
{
    public function __construct(
        protected OnboardingCompanyInterface $onboardingCompanyRepository,
        protected CompanyUserInterface $companyUserRepository,
        protected UserInterface $userRepository
    ){}

    public function store(User $user, array $data): Company
    {
        return DB::transaction(function() use ($user, $data)
        {
            $lockedUser = $this->userRepository->findByUpdate(
                id: $user->id,
                columns: [
                    'id',
                    'profile_type',
                    'onboarding_completed_at'
                ]

            );

            if ($lockedUser->profile_type !== ProfileTypes::COMPANY) abort(403, 'Este cadastro não pertecen ao perfil de empresa.');

            if ($lockedUser->onboarding_completed_at  !== null) abort(409, 'O cadastro já foi concluído.');

            $company = $this->onboardingCompanyRepository->create($data);

            $this->companyUserRepository->create([
                'user_id' => $lockedUser->id,
                'company_id' => $company->id,
                'role' => 'responsible'
            ]);

            $this->userRepository->update($lockedUser, [
                'onboarding_completed_at' => now()
            ]);

            return $company;
        });
    }
}
