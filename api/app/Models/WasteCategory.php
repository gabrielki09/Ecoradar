<?php

namespace App\Models;

use Illuminate\Console\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
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
}
