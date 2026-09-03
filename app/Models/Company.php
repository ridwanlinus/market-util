<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'website',
        'industry',
        'logo',
        'description',
        'created_by',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function metaPages(): HasMany
    {
        return $this->hasMany(MetaPage::class);
    }

    public function googleAdsCampaigns(): HasMany
    {
        return $this->hasMany(GoogleAdsCampaign::class);
    }

    public function gaProperties(): HasMany
    {
        return $this->hasMany(GaProperty::class);
    }
}