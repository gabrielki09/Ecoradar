<?php

namespace App\Models\Collection;

use App\Enum\CollectionRequest\CollectionRequestStatus;
use App\Models\Collector\CollectorProfile;
use App\Models\General\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'id',
    'requester_user_id',
    'city_id',
    'cep',
    'neighborhood',
    'address',
    'status',
    'accepted_company_id',
    'accepted_collector_profile_id',
    'acceptance_date',
    'withdrawal_date',
    'completion_date',
    'canceled_date',
])]
class CollectionRequest extends Model
{
    protected function casts()
    {
        return [
            'status' => CollectionRequestStatus::class,
            'acceptance_date' => 'datetime',
            'withdrawal_date' => 'datetime',
            'completion_date' => 'datetime',
            'canceled_date' => 'datetime',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CollectionRequestItem::class);
    }

    public function acceptedCollectorProfile(): BelongsTo
    {
        return $this->belongsTo(CollectorProfile::class, 'accepted_collector_profile_id');
    }

    public function requester() : BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_user_id')->select(['users.id', 'users.name']);
    }

    public function history(): HasMany
    {
        return $this->hasMany(CollectionHistory::class, 'id');
    }

    public function hours(): HasOne
    {
        return $this->hasOne(CollectionSchedule::class);
    }
}
