<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calculation extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'kind',
        'inputs',
        'result',
        'meta_post_id',
    ];

    protected function casts(): array
    {
        return [
            'inputs' => 'array',
            'result' => 'float',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function metaPost(): BelongsTo
    {
        return $this->belongsTo(MetaPost::class);
    }
}