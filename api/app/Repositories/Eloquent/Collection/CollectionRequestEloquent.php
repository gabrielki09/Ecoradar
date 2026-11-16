<?php

namespace App\Repositories\Eloquent\Collection;

use App\Enum\CollectionRequest\CollectionRequestStatus;
use App\Models\Collection\CollectionRequest;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\CollectionRequestInterface;

class CollectionRequestEloquent extends BaseEloquent implements CollectionRequestInterface
{
    protected array $filterable = [
        'requester_user_id',
        'cep',
        'neighborhood',
        'address',
        'status',
        'accepted_company_id',
        'accepted_collector_profile_id',
        'acceptance_date',
        'withdrawal_date',
        'completion_date',
    ];

    protected array $allowedRelationships = [
        'items',
        'requester'
    ];

    protected function modelClass(): string
    {
        return CollectionRequest::class;
    }

    public function allForRequester(int $requesterId, array $parameters = [])
    {
        $query = $this->model->newQuery()
            ->where('requester_user_id', $requesterId);

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
                (int) ($parameters['paginate'] ?? 15),
                100
            )
        );

        return $query->paginate(
            perPage: $perPage
        );
    }

    /**
     * @param array<int> $cityIds
     * @param array<int> $categoryIds
     */
    public function availableFor(
        array $cityIds,
        array $categoryIds,
        array $parameters = []
    ) {
        $query = $this->model->newQuery()
            ->where('status', CollectionRequestStatus::OPEN->value)
            ->whereIn('city_id', $cityIds)
            ->whereIn('waste_category_id', $categoryIds);

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
                (int) ($parameters['paginate'] ?? 15),
                100
            )
        );

        return $query->paginate(
            perPage: $perPage
        );
    }
}
