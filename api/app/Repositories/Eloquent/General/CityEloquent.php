<?php

namespace App\Repositories\Eloquent\General;

use App\Models\General\City;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\General\CityInterface;

class CityEloquent extends BaseEloquent implements CityInterface
{
    protected array $filterable = [
        'id',
        'codigo_tom',
        'codigo_ibge',
        'municipio_tom',
        'municipio_ibge',
        'uf',
    ];

    protected function modelClass(): string
    {
        return City::class;
    }
}
