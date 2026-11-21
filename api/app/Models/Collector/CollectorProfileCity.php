<?php

namespace App\Models\Collector;

use App\Trait\CanBeRecovered;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id',
    'collector_profile_id',
    'city_id',
    'deletead_at'
])]
class CollectorProfileCity extends Model
{
    use SoftDeletes, CanBeRecovered;

    public function collectorProfile(): BelongsTo
    {
        return $this->belongsTo(CollectorProfile::class);
    }

    public function cities(): BelongsTo
    {
        return $this->belongsTo(CollectorProfile::class);
    }
}
