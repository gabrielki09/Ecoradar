<?php

namespace App\Services\Onboarding;

use App\Enum\ProfileTypes;
use App\Models\CollectorProfile;
use App\Models\User;
use App\Repositories\Interface\Onboarding\OnboardingCollectorProfileInterface;
use App\Repositories\Interface\UserInterface;
use Illuminate\Support\Facades\DB;

class OnboardingCollectorService
{
    public function __construct(
        protected OnboardingCollectorProfileInterface $onboardingCollectorProfileRepository,
        protected UserInterface $userRepository
    ){}

    public function store(User $user): CollectorProfile
    {
        return DB::transaction(function() use ($user)
        {
            $lockedUser = $this->userRepository->findByUpdate(
                id: $user->id,
                columns: [
                    'id',
                    'profile_type',
                    'onboarding_completed_at'
                ]
            );

            if ($lockedUser->profile_type !== ProfileTypes::COLLECTOR) abort(403, 'Este cadastro não pertecen ao perfil de coletor.');

            if ($lockedUser->onboarding_completed_at  !== null) abort(409, 'O cadastro já foi concluído.');

            $collectorProfile = $this->onboardingCollectorProfileRepository->create([
                'user_id' => $lockedUser->id,
            ]);

            $this->userRepository->update($lockedUser, [
                'onboarding_completed_at' => now()
            ]);

            return $collectorProfile;
        });
    }
}
