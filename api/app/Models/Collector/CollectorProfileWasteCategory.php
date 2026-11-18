<?php

namespace App\Models\Collector;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id',
    'collector_profile_id',
    'waste_category_id'
])]
class CollectorProfileWasteCategory extends Model
{
    use SoftDeletes;

    public function collectorProfile(): BelongsTo
    {
        return $this->belongsTo(CollectorProfile::class);
    }
}
