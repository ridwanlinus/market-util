<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaPost extends Model
{
    protected $fillable = [
        'company_id',
        'meta_page_id',
        'post_id',
        'kind',
        'message',
        'posted_at',
        'impressions',
        'reach',
        'likes',
        'comments',
        'shares',
        'saves',
        'video_views',
        'link_clicks',
        'followers_count',
        'spend',
    ];

    protected function casts(): array
    {
        return [
            'posted_at' => 'date',
            'spend' => 'float',
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

    public function calculations(): HasMany
    {
        return $this->hasMany(Calculation::class);
    }

    public function totalInteractions(): int
    {
        return $this->likes + $this->comments + $this->shares + $this->saves;
    }
}