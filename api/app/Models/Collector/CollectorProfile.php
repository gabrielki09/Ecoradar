<?php

namespace App\Models\Collector;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id',
    'user_id',
    'service_description',
    'is_active'
])]
class CollectorProfile extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wasteCategories(): HasMany
    {
        return $this->hasMany(CollectorProfileWasteCategory::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(CollectorProfileCity::class);
    }
}
