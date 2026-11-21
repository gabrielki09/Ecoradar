<?php

namespace App\Repositories\Eloquent\Collection;

use App\Models\Collection\CollectionPointWasteCategory;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\CollectionPointWasteCategoryInterface;

class CollectionPointWasteCategoryEloquent extends BaseEloquent implements CollectionPointWasteCategoryInterface
{
    protected array $filterable = [
        'id',
        'collection_point_id',
        'waste_category_id',
    ];

    protected array $allowedRelationships = [
        'collectionPoint',
        'wasteCategory',
    ];

    protected function modelClass(): string
    {
        return CollectionPointWasteCategory::class;
    }
}
