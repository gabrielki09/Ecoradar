<?php

namespace App\Services\Collection;

use App\Enum\Collection\CollectionResponsibleType;
use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Models\User;
use App\Models\Collection\CollectionPoint;
use App\Models\Collection\CollectionPointOpeningHour;
use App\Models\Collection\CollectionPointWasteCategory;
use App\Repositories\Interface\Collection\CollectionPointInterface;
use App\Repositories\Interface\Collection\CollectionPointOpeningHourInterface;
use App\Repositories\Interface\Collection\CollectionPointWasteCategoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CollectionPointService
{
    public function __construct(
        protected CollectionPointInterface $collectionPointRepository,
        protected CollectionPointOpeningHourInterface $collectionPointOpeningHourRepository,
        protected CollectionPointWasteCategoryInterface $collectionPointWasteCategoryRepository
    ){}

    public function all(
        array $parameters
    ) {
        return $this->collectionPointRepository->all(
            parameters: $parameters
        );
    }

    public function allForProvider(
        User $user,
        array $parameters
    ) {
        return $this->collectionPointRepository->allForProvider(
            provider: resolveProvider(
                user: $user,
                companyId: $parameters['company_id'] ?? null,
                responsibleOnly: true
            ),
            parameters: $parameters
        );
    }

    private function buildCollectionPointData(
        CollectorProfile|Company $responsibleProvider,
        array $data
    ): array {
        return [
            ...array_intersect_key($data, array_flip([
                'name', 'cep', 'neighborhood', 'address', 'guidelines', 'contact', 'latitude', 'longitude'
            ])),
            'responsible_operator_type' =>
                $responsibleProvider instanceof CollectorProfile
                    ? CollectionResponsibleType::COLLECTOR
                    : CollectionResponsibleType::COMPANY,
            'responsible_operator_id' => $responsibleProvider->id
        ];
    }

    public function store(
        User $user,
        array $data
    ) {
        return DB::transaction(function() use ($user, $data) {
            $responsibleProvider = resolveProvider(
                $user,
                $data['company_id'] ?? null,
                responsibleOnly: true
            );

            $collectionPoint = $this->collectionPointRepository->create($this->buildCollectionPointData($responsibleProvider, $data));

            if (array_key_exists('opening_hours', $data))
            {
                foreach ($data['opening_hours'] as $openingHour) {
                    $this->collectionPointOpeningHourRepository->updateOrCreate(
                        uniqueColumns: [
                            'collection_point_id' => $collectionPoint->id,
                            'opening_day' => $openingHour['opening_day'],
                        ],

                        data: [
                            'collection_point_id' => $collectionPoint->id,
                            'opening_day' => $openingHour['opening_day'],
                            'opens_at' => $openingHour['opens_at'],
                            'closes_at' => $openingHour['closes_at']
                        ]
                    );
                }
            }

            if (array_key_exists('waste_categories', $data))
            {
                foreach ($data['waste_categories'] as $wasteCategory) {
                    $wasteCategoryId = $wasteCategory['waste_category_id'];

                    $this->collectionPointWasteCategoryRepository->updateOrCreate(
                        uniqueColumns: [
                            'collection_point_id' => $collectionPoint->id,
                            'waste_category_id' => $wasteCategoryId,
                        ],

                        data: [
                            'collection_point_id' => $collectionPoint->id,
                            'waste_category_id' => $wasteCategoryId,
                        ]
                    );
                }
            }

            return $collectionPoint->load([
                'openingHours',
                'collectionWasteCategory'
            ]);
        });
    }

    private function buildFindByParams(
        CollectorProfile|Company $provider,
        int $id,
    ): array {
        $default = [
            'id' => $id,
            'responsible_operator_type' => $provider instanceof CollectorProfile
                ? CollectionResponsibleType::COLLECTOR
                : CollectionResponsibleType::COMPANY,
            'responsible_operator_id' => $provider->id,
        ];

        return $default;
    }

    public function show(
        User $user,
        array $parameters,
        int $id,
    ) {
        $provider = resolveProvider(
            $user,
            $parameters['company_id'] ?? null,
            responsibleOnly: true
        );

        $collectionPoint = $this->collectionPointRepository->findBy(
            parameters: $this->buildFindByParams(
                    provider: $provider,
                    id: $id
                ),
            relationships: ['openingHours', 'collectionWasteCategory']
        );

        if (!$collectionPoint) throw new ModelNotFoundException('Ponto de coleta não localizado.');

        return $collectionPoint;
    }

    public function update(
        User $user,
        array $data,
        int $id
    ) {
        return DB::transaction(function() use ($user, $data, $id) {
            $provider = resolveProvider(
                $user,
                $data['company_id'] ?? null,
                responsibleOnly: true
            );

            /** @var CollectionPoint|null $collectionPoint */
            $collectionPoint = $this->collectionPointRepository->findBy(
                $this->buildFindByParams(
                    provider: $provider,
                    id: $id
                )
            );

            if (!$collectionPoint) throw new ModelNotFoundException('Ponto de coleta não localizado.');

            $this->collectionPointRepository->update(
                $collectionPoint,
                $this->buildCollectionPointData($provider, $data)
            );

            if (array_key_exists('opening_hours', $data))
            {
                foreach ($data['opening_hours'] as $openingHour) {
                    $openingDay = $openingHour['opening_day'];

                    $hour = $collectionPoint->openingHours()
                        ->withTrashed()
                        ->firstOrNew(['opening_day' => $openingDay]);

                    if ($openingHour['delete'] ?? false)
                    {
                        if ($hour->exists && ! $hour->trashed())
                        {
                            $hour->delete();
                        }

                        continue;
                    }

                    $hour->fill([
                        'opens_at' => $openingHour['opens_at'],
                        'closes_at' => $openingHour['closes_at'],
                    ]);

                    $hour->exists && $hour->trashed()
                        ? $hour->restore()
                        : $hour->save();
                }
            }

            if (array_key_exists('waste_categories', $data))
            {
                foreach ($data['waste_categories'] as $wasteCategoryData) {
                    $wasteCategoryId = $wasteCategoryData['waste_category_id'];

                    $wasteCategory = $collectionPoint->collectionWasteCategory()
                        ->withTrashed()
                        ->firstOrNew([
                            'waste_category_id' => $wasteCategoryId
                        ]);

                    if ($wasteCategoryData['delete'] ?? false)
                    {
                        if ($wasteCategory->exists && ! $wasteCategory->trashed())
                        {
                            $wasteCategory->delete();
                        }

                        continue;
                    }

                    $wasteCategory->fill([
                        'waste_category_id' => $wasteCategoryId
                    ]);

                    $wasteCategory->exists && $wasteCategory->trashed()
                        ? $wasteCategory->restore()
                        : $wasteCategory->save();
                }
            }

            return $collectionPoint->load([
                'openingHours',
                'collectionWasteCategory'
            ]);
        });
    }

    public function destroy(
        User $user,
        array $parameters,
        int $id
    ) {
        return DB::transaction(function() use ($user, $parameters, $id) {
            $provider = resolveProvider(
                $user,
                $parameters['company_id'] ?? null,
                responsibleOnly: true
            );

            /** @var CollectionPoint|null $collectionPoint */
            $collectionPoint = $this->collectionPointRepository->findBy(
                $this->buildFindByParams(
                    provider: $provider,
                    id: $id
                )
            );

            if (!$collectionPoint) throw new ModelNotFoundException('Ponto de coleta não localizado.');

            /** @var CollectionPointOpeningHour|null $openingHour */
            foreach ($collectionPoint->openingHours()->get() as $openingHour) {
                $openingHour->deleted_with_point = true;
                $openingHour->save();
                $openingHour->delete();
            }

            /** @var CollectionPointWasteCategory|null $wasteCategory */
            foreach ($collectionPoint->collectionWasteCategory()->get() as $wasteCategory) {
                $wasteCategory->deleted_with_point = true;
                $wasteCategory->save();
                $wasteCategory->delete();
            }

            $this->collectionPointRepository->delete($collectionPoint);
        });
    }

    public function recovery(
        User $user,
        array $parameters,
        int $id
    ) {
        return DB::transaction(function() use ($user, $parameters, $id) {
            $provider = resolveProvider(
                $user,
                $parameters['company_id'] ?? null,
                responsibleOnly: true
            );

            /** @var CollectionPoint|null $collectionPoint */
            $collectionPoint = $this->collectionPointRepository->findWithTrashed(
                $this->buildFindByParams(
                    provider: $provider,
                    id: $id
                )
            );

            if (!$collectionPoint || !$collectionPoint->trashed()) throw new ModelNotFoundException('Ponto de coleta não localizado.');

            $collectionPoint->restore();

            /** @var CollectionPointOpeningHour|null $openingHour */
            foreach ($collectionPoint->openingHours()->onlyTrashed()->where('deleted_with_point', true)->get() as $openingHour) {
                $openingHour->restore();
                $openingHour->deleted_with_point = false;
                $openingHour->save();
            }

            /** @var CollectionPointWasteCategory|null $wasteCategory */
            foreach ($collectionPoint->collectionWasteCategory()->onlyTrashed()->where('deleted_with_point', true)->get() as $wasteCategory) {
                $wasteCategory->restore();
                $wasteCategory->deleted_with_point = false;
                $wasteCategory->save();
            }

            return $collectionPoint->load([
                'openingHours',
                'collectionWasteCategory',
            ]);
        });
    }

    public function allPublic(array $parameters) {
        return $this->collectionPointRepository->allPublic(
            parameters: $parameters
        );
    }    
}
