<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    public const STATUS = ['draft', 'pending', 'approved', 'rejected'];

    protected $fillable = [
        'company_id',
        'user_id',
        'title',
        'type',
        'slides_count',
        'status',
        'design',
        'cover_path',
        'files',
        'caption',
        'platform',
        'scheduled_at',
        'approval_note',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'design' => 'array',
            'files' => 'array',
            'slides_count' => 'integer',
            'scheduled_at' => 'datetime',
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? asset('storage/' . $this->cover_path) : null;
    }

    public function fileUrls(): array
    {
        return collect($this->files ?? [])->map(fn ($f) => asset('storage/' . $f))->all();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Menunggu Approval',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending' => 'orange',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }
}