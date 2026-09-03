<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetaInsight extends Model
{
    protected $fillable = [
        'company_id',
        'meta_page_id',
        'date',
        'impressions',
        'reach',
        'engagement',
        'likes',
        'comments',
        'shares',
        'saves',
        'clicks',
        'spend',
        'ctr',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'spend' => 'float',
            'ctr' => 'float',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function metaPage(): BelongsTo
    {
        return $this->belongsTo(MetaPage::class);
    }
}