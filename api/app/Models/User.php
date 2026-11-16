<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enum\ProfileTypes;
use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Models\Company\CompanyUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'address',
    'document',
    'profile_type',
    'onboarding_completed_at'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,  HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'profile_type' => ProfileTypes::class,
            'onboarding_completed_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function companyUsers() : HasMany
    {
        return $this->hasMany(CompanyUser::class)
                ->withTimestamps();
    }

    public function companies() : BelongsToMany
    {
        return $this->BelongsToMany(
            Company::class,
            'company_users',
            'user_id',
            'company_id'
        )->withTimestamps();
    }

    public function collectorProfile(): HasOne
    {
        return $this->hasOne(CollectorProfile::class);
    }
}
