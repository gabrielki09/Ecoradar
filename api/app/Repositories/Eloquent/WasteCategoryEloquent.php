<?php

namespace App\Repositories\Eloquent;

use App\Models\WasteCategory;
use App\Repositories\Interface\WasteCategoryInterface;

class WasteCategoryEloquent extends BaseEloquent implements WasteCategoryInterface
{
    protected function modelClass(): string
    {
        return WasteCategory::class;
    }
}
