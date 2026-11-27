<?php

namespace App\Models\Collection;

use App\Casts\Time\TimeCast;
use App\Enum\Days\WeekDay;
use App\Http\Requests\Collection\CollectedRequest;
use App\Trait\CanBeRecovered;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'collection_request_id',
    'observation',
    'scheduled_day',
    'scheduled_time_start',
    'scheduled_time_end'
])]
class CollectionSchedule extends Model
{
    use SoftDeletes, CanBeRecovered;

    protected function casts()
    {
        return [
            'scheduled_day' => WeekDay::class,
            'scheduled_time_start' => TimeCast::class,
            'scheduled_time_end' => TimeCast::class
        ];
    }

    public function collectionRequest(): HasMany
    {
        return $this->hasMany(CollectedRequest::class);
    }
}
