<?php

namespace App\Models\Company;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'document',
    'company_name',
    'responsible',
    'cep',
    'neighborhood',
    'address',
    'contact_email',
])]
class Company extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }
}
