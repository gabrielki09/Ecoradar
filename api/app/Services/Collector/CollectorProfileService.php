<?php

namespace App\Services\Collector;

use App\Models\Collector\CollectorProfile;
use App\Models\User;
use App\Repositories\Eloquent\Collector\CollectorProfileCityEloquent;
use App\Repositories\Interface\Collector\CollectorProfileWasteCategoryInterface;
use App\Repositories\Interface\Onboarding\OnboardingCollectorProfileInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CollectorProfileService
{
    public function __construct(
        protected OnboardingCollectorProfileInterface $collectorProfileRepository,
        protected CollectorProfileWasteCategoryInterface $collectorProfileWasteCategoryRepository,
        protected CollectorProfileCityEloquent $collectorProfileCityRepository
    ){}

    public function show(int $id)
    {
        return $this->collectorProfileRepository->findBy(
            parameters: [
                'user_id' => $id
            ]
        );
    }

    public function update(array $data, int $collectorProfileId)
    {
        return DB::transaction(function() use ($data, $collectorProfileId) {
            $collectorProfile = $this->collectorProfileRepository->findByUpdate(
                $collectorProfileId
            );

            if (!$collectorProfile) throw new ModelNotFoundException('Requisição não localizada.');

            if (array_key_exists('cities', $data))
            {
                foreach ($data['cities'] as $city) {
                    if ($city['delete'])
                    {
                        $this->collectorProfileCityRepository->delete();
                    }


                    $this->collectorProfileCityRepository->update(
                        $collectorProfile, [
                        'city_id' => $city['city_id']
                    ]);
                }
            }

            if (array_key_exists('waste_category', $data))
            {
                foreach ($data['waste_category'] as $wasteCategory) {
                    $this->collectorProfileWasteCategoryRepository->update(
                        $collectorProfile, [
                        'waste_category_id' => $wasteCategory
                    ]);
                }
            }

            $this->collectorProfileRepository->update(
                $collectorProfile,
                [
                    'service_description' => $data['service_description']
                ]
            );

            return $collectorProfile->refresh();
        });
    }

    public function store(User $user, array $data): CollectorProfile
    {
        return DB::transaction(function() use ($user, $data) {
            $collectorProfile = $this->collectorProfileRepository->findBy(
                parameters: [
                    'user_id' => $user->id
                ]
            );

            if (!$collectorProfile) throw new ModelNotFoundException('Requisição não localizada.');

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

            return $collectorProfile;
        });
    }
}
