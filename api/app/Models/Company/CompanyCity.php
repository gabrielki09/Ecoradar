<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id',
    'company_id',
    'city_id',
])]
class CompanyCity extends Model
{
    use SoftDeletes;
}
