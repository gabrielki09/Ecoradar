<?php

namespace App\Repositories\Eloquent\Collector;

use App\Models\Collector\CollectorProfileWasteCategory;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collector\CollectorProfileWasteCategoryInterface;

class CollectorProfileWasteCategoryEloquent extends BaseEloquent implements CollectorProfileWasteCategoryInterface
{
    protected function modelClass(): string
    {
        return CollectorProfileWasteCategory::class;
    }
}
