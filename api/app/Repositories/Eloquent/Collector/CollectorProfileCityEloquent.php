<?php

namespace App\Repositories\Eloquent\Collector;

use App\Models\Collector\CollectorProfileCity;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collector\CollectorProfileCityInterface;

class CollectorProfileCityEloquent extends BaseEloquent implements CollectorProfileCityInterface
{
    protected function modelClass(): string
    {
        return CollectorProfileCity::class;
    }
}
