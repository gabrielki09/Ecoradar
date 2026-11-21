<?php

namespace App\Models\Collection;

use App\Casts\Time\TimeCast;
use App\Enum\Days\WeekDay;
use App\Trait\CanBeRecovered;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id',
    'collection_point_id',
    'opening_day',
    'opens_at',
    'closes_at'
])]
#[Hidden(['deleted_with_point'])]
class CollectionPointOpeningHour extends Model
{
    use SoftDeletes, CanBeRecovered;

    protected function casts()
    {
        return [
            'opening_day' => WeekDay::class,
            'closes_at' => TimeCast::class,
            'opens_at' => TimeCast::class
        ];
    }

    public function collectionPoint(): BelongsTo
    {
        return $this->belongsTo(CollectionPoint::class);
    }
}
