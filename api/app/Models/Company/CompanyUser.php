<?php

namespace App\Models\Company;

use App\Enum\CompanyUsers\RoleUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id',
    'company_id',
    'role'
])]
class CompanyUser extends Model
{
    protected $casts = [
        'role' => RoleUser::class
    ];
}
