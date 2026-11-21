<?php

namespace App\Models\Collection;

use App\Models\WasteCategory;
use App\Trait\CanBeRecovered;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'id',
    'collection_point_id',
    'waste_category_id',
])]
#[Hidden(['deleted_with_point'])]
class CollectionPointWasteCategory extends Model
{
    use SoftDeletes, CanBeRecovered;

    public function collectionPoint(): BelongsTo
    {
        return $this->belongsTo(CollectionPoint::class);
    }

    public function wasteCategory(): BelongsTo
    {
        return $this->belongsTo(WasteCategory::class);
    }
}
