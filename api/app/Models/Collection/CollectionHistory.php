<?php

namespace App\Models\Collection;

use App\Enum\CollectionRequest\CollectionRequestStatus;
use App\Enum\ProfileTypes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'id',
    'collection_request_id',
    'request_operator_type',
    'request_operator_id',
    'old_status',
    'new_status',
    'acceptance_date',
    'withdrawal_date',
    'completion_date',
    'canceled_date',
    'cancellation_reason'
])]
class CollectionHistory extends Model
{
    protected function casts()
    {
        return [
            'request_operator_type' => ProfileTypes::class,
            'old_status' => CollectionRequestStatus::class,
            'new_status' => CollectionRequestStatus::class,
            'acceptance_date' => 'datetime',
            'withdrawal_date' => 'datetime',
            'completion_date' => 'datetime',
            'canceled_date' => 'datetime',
        ];
    }

    public function collectionRequest(): BelongsToMany
    {
        return $this->belongsToMany(CollectionRequest::class)->withTimestamps();
    }
}
