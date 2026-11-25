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
            ],
            relationships: ['cities', 'wasteCategories']
        );
    }

    public function update(array $data, int $collectorProfileId)
    {
        return DB::transaction(function() use ($data, $collectorProfileId) {
            $collectorProfile = $this->collectorProfileRepository->findByUpdate(
                $collectorProfileId
            );

            if (isset($data['cities']))
            {
                foreach ($data['cities'] as $city) {
                    $cityId = $city['city_id'];

                    if ($city['delete'] ?? false)
                    {
                        $collectorProfile->cities()
                            ->where('city_id', $cityId)
                            ->delete();

                        continue;
                    }

                    $link = $collectorProfile->cities()
                        ->withTrashed()
                        ->firstOrCreate(['city_id' => $cityId]);
                    if ($link->trashed()) {
                        $link->restore();
                    }
                }
            }

            if (isset($data['waste_categories']))
            {
                foreach ($data['waste_categories'] as $wasteCategory) {
                    $wasteCategoryId = $wasteCategory['waste_category_id'];

                    if ($wasteCategory['delete'] ?? false)
                    {
                        $collectorProfile->wasteCategories()
                            ->where('waste_category_id', $wasteCategoryId)
                            ->delete();

                        continue;
                    }

                    $link = $collectorProfile->wasteCategories()
                        ->withTrashed()
                        ->firstOrCreate(['waste_category_id' => $wasteCategoryId]);
                    if ($link->trashed()) {
                        $link->restore();
                    }
                }
            }

            if (array_key_exists('service_description', $data)) $this->collectorProfileRepository->update($collectorProfile, ['service_description' => $data['service_description']]);

            return $collectorProfile->load(['cities', 'wasteCategories']);
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
