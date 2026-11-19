<?php

namespace App\Models\General;

use App\Models\Collection\CollectionRequest;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'codigo_tom',
    'codigo_ibge',
    'municipio_tom',
    'municipio_ibge',
    'uf',
])]
class City extends Model
{
    public function collectionRequest(): BelongsTo
    {
        return $this->belongsTo(CollectionRequest::class);
    }
}
