<?php

namespace App\Repositories\Eloquent\Collection;

use App\Models\Collection\CollectionPointOpeningHour;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\CollectionPointOpeningHourInterface;

class CollectionPointOpeningHourEloquent extends BaseEloquent implements CollectionPointOpeningHourInterface
{
    protected function modelClass(): string
    {
        return CollectionPointOpeningHour::class;
    }
}
