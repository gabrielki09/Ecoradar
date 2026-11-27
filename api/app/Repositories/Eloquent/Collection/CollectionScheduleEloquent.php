<?php

namespace App\Repositories\Eloquent\Collection;

use App\Models\Collection\CollectionSchedule;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\CollectionScheduleInterface;

class CollectionScheduleEloquent extends BaseEloquent implements CollectionScheduleInterface
{
    protected function modelClass(): string
    {
        return CollectionSchedule::class;
    }
}
