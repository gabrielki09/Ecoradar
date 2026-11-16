<?php

namespace App\Models\Collection;

use App\Models\WasteCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'collection_request_id',
    'waste_category_id',
    'description',
    'approximate_quantity'
])]
class CollectionRequestItem extends Model
{
    protected function casts()
    {
        return [
            'approximate_quantity' => 'decimal:2'
        ];
    }

    public function collectionRequest(): BelongsTo
    {
        return $this->belongsTo(CollectionRequest::class);
    }

    public function wasteCategories(): HasMany
    {
        return $this->hasMany(WasteCategory::class);
    }
}
