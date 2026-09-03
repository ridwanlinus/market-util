<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaPage extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'page_id',
        'followers_count',
        'access_token',
        'connected_at',
    ];

    protected function casts(): array
    {
        return [
            'connected_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(MetaPost::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(MetaInsight::class);
    }
}