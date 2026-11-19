<?php

namespace App\Services\General;

use App\Repositories\Interface\General\CityInterface;

class CityService
{
    public function __construct(
        protected CityInterface $cityRepository
    ){}

    public function all(array $parameters)
    {
        return $this->cityRepository->all(
            parameters: $parameters
        );
    }
}
