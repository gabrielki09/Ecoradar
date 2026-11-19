<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\Filters\FilterV1Request;
use App\Services\General\CityService;

class CityController extends Controller
{
    public function __construct(
        protected CityService $cityService
    ){}

    public function all(FilterV1Request $req)
    {
        return apiSuccess(
            'Todas as cidades',
            [
                'cities' => $this->cityService->all(
                    parameters: $req->validated()
                )
            ]
        );
    }
}
