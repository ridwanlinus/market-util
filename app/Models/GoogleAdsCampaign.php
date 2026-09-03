<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAdsCampaign extends Model
{
    protected $fillable = ['company_id', 'name', 'campaign_id', 'status'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function insights(): HasMany
    {
        return $this->hasMany(GoogleAdsInsight::class);
    }
}