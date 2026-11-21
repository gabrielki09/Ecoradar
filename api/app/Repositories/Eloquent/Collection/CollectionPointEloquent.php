<?php

namespace App\Repositories\Eloquent\Collection;

use App\Models\Collection\CollectionPoint;
use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Enum\Collection\CollectionResponsibleType;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\CollectionPointInterface;

class CollectionPointEloquent extends BaseEloquent implements CollectionPointInterface
{
    protected array $filterable = [
        'id',
        'name',
        'cep',
        'neighborhood',
        'address',
        'guidelines',
        'contact',
        'responsible_operator_type',
        'responsible_operator_id',
        'status'
    ];

    protected array $allowedRelationships = [
        'openingHours',
        'collectionWasteCategory',
    ];

    protected function modelClass(): string
    {
        return CollectionPoint::class;
    }

    public function allForProvider(CollectorProfile|Company $provider, array $parameters = [])
    {
        $query = $this->model->newQuery()
            ->where('responsible_operator_id', $provider->id)
            ->where('responsible_operator_type', $provider instanceof CollectorProfile
                ? CollectionResponsibleType::COLLECTOR->value
                : CollectionResponsibleType::COMPANY->value)
            ->with(['openingHours', 'collectionWasteCategory']);

        if ($parameters['include_trashed'] ?? false) {
            $query->withTrashed();
        }

        $this->applyRelationships(
            $query,
            $parameters['relationships'] ?? []
        );

        $this->applyFilters(
            $query,
            $parameters['filtersV1'] ?? []
        );

        $this->applyOrderBy(
            $query,
            $parameters['orderBy'] ?? []
        );

        $perPage = max(
            1,
            min(
                (int) ($parameters['per_page'] ?? 15),
                100
            )
        );

        return $query->paginate(
            perPage: $perPage
        );
    }
}
