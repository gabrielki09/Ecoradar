<?php

namespace App\Repositories\Eloquent\CompanyUser;

use App\Models\Company\CompanyUser;
use App\Repositories\Eloquent\BaseEloquent;
use App\Repositories\Interface\CompanyUser\CompanyUserInterface;

class CompanyUserEloquent extends BaseEloquent implements CompanyUserInterface
{
    protected function modelClass(): string
    {
        return CompanyUser::class;
    }
}
