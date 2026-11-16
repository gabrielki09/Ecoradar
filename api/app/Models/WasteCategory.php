<?php

namespace App\Models;

use App\Models\Company\Company;
use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id',
    'name',
    'is_system',
    'deletad_at'
])]

#[Hidden([
    'is_system'
])]
class WasteCategory extends Model
{
    use SoftDeletes;

    protected function casts()
    {
        return [
            'is_system' => 'boolean'
        ];
    }

    public function compay(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
