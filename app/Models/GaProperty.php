<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GaProperty extends Model
{
    protected $fillable = ['company_id', 'name', 'property_id', 'website'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(GaInsight::class);
    }
}