<?php

namespace App\Models\Company;

use App\Models\User;
use App\Models\WasteCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'document',
    'company_name',
    'responsible',
    'cep',
    'neighborhood',
    'address',
    'contact_email',
])]
class Company extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    // Categorias personalizadas criadas por esta empresa (waste_categories.company_id)
    public function customWasteCategories(): HasMany
    {
        return $this->hasMany(WasteCategory::class);
    }

    // Categorias que a empresa aceita, inclusive as do seeder
    public function acceptedWasteCategories(): BelongsToMany
    {
        return $this->belongsToMany(WasteCategory::class);
    }
}
