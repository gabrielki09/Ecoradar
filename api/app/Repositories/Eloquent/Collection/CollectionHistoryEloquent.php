<?php

namespace App\Repositories\Eloquent\Collection;

use App\Models\Collection\CollectionHistory;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\CollectionHistoryInterface;

class CollectionHistoryEloquent extends BaseEloquent implements CollectionHistoryInterface
{
    protected function modelClass(): string
    {
        return CollectionHistory::class;
    }
}
