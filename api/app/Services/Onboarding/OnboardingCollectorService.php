<?php

namespace App\Services\Onboarding;

use App\Enum\ProfileTypes;
use App\Models\Collector\CollectorProfile;
use App\Models\User;
use App\Repositories\Eloquent\Collector\CollectorProfileCityEloquent;
use App\Repositories\Interface\Collector\CollectorProfileWasteCategoryInterface;
use App\Repositories\Interface\Onboarding\OnboardingCollectorProfileInterface;
use App\Repositories\Interface\UserInterface;
use Illuminate\Support\Facades\DB;

class OnboardingCollectorService
{
    public function __construct(
        protected OnboardingCollectorProfileInterface $onboardingCollectorProfileRepository,
        protected CollectorProfileWasteCategoryInterface $collectorProfileWasteCategoryRepository,
        protected CollectorProfileCityEloquent $collectorProfileCityRepository,
        protected UserInterface $userRepository
    ){}

    public function store(User $user, array $data): CollectorProfile
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

            if ($lockedUser->profile_type !== ProfileTypes::COLLECTOR) abort(403, 'Este cadastro não pertecen ao perfil de coletor.');

            if ($lockedUser->onboarding_completed_at  !== null) abort(409, 'O cadastro já foi concluído.');

            $collectorProfile = $this->onboardingCollectorProfileRepository->create([
                'user_id' => $lockedUser->id,
                'service_description' => $data['service_description']
            ]);

            foreach ($data['cities'] as $cityId) {
                $this->collectorProfileCityRepository->create([
                    'collector_profile_id' => $collectorProfile->id,
                    'city_id' => $cityId
                ]);
            }

            foreach ($data['waste_category'] as $wasteCategory) {
                $this->collectorProfileWasteCategoryRepository->create([
                    'collector_profile_id' => $collectorProfile->id,
                    'waste_category_id' => $wasteCategory
                ]);
            }

            $this->userRepository->update($lockedUser, [
                'onboarding_completed_at' => now()
            ]);

            return $collectorProfile;
        });
    }
}
