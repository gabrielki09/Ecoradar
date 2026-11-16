<?php

namespace App\Repositories\Eloquent\Collection;

use App\Models\Collection\CollectionRequestItem;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\Collection\CollectionRequestInterface;

class CollectionRequestItemEloquent extends BaseEloquent implements CollectionRequestInterface
{
    protected function modelClass(): string
    {
        return CollectionRequestItem::class;
    }
}

