<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaInsight extends Model
{
    protected $fillable = [
        'company_id',
        'property_id',
        'date',
        'users',
        'new_users',
        'sessions',
        'pageviews',
        'avg_session_duration',
        'bounce_rate',
        'top_pages',
        'channels',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'avg_session_duration' => 'float',
            'bounce_rate' => 'float',
            'top_pages' => 'array',
            'channels' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(GaProperty::class);
    }
}