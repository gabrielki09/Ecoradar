<?php

namespace App\Models\Collection;

use App\Enum\Collection\CollectionPointStatus;
use App\Enum\Collection\CollectionResponsibleType;
use App\Models\Collector\CollectorProfile;
use App\Models\Company\Company;
use App\Trait\CanBeRecovered;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id',
    'name',
    'cep',
    'neighborhood',
    'address',
    'guidelines',
    'contact',
    'responsible_operator_type',
    'responsible_operator_id',
    'status'
])]
class CollectionPoint extends Model
{
    use SoftDeletes, CanBeRecovered;

    protected function casts()
    {
        return [
            'responsible_operator_type' => CollectionResponsibleType::class,
            'status' => CollectionPointStatus::class,
        ];
    }

    public function collectorProfile(): BelongsTo
    {
        return $this->belongsTo(CollectorProfile::class, 'responsible_operator_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'responsible_operator_id', 'id');
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(CollectionPointOpeningHour::class);
    }

    public function collectionWasteCategory(): HasMany
    {
        return $this->hasMany(CollectionPointWasteCategory::class);
    }
}
