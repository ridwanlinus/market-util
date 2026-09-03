<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleAdsInsight extends Model
{
    protected $fillable = [
        'company_id',
        'campaign_id',
        'date',
        'impressions',
        'clicks',
        'ctr',
        'cpc',
        'cost',
        'conversions',
        'conversion_value',
        'roas',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'ctr' => 'float',
            'cpc' => 'float',
            'cost' => 'float',
            'conversion_value' => 'float',
            'roas' => 'float',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(GoogleAdsCampaign::class);
    }
}