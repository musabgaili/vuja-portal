<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CrmActivity extends Model
{
    protected $fillable = [
        'subject_type', 'subject_id', 'user_id', 'created_by',
        'type', 'summary', 'notes', 'due_at', 'status', 'done_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'done_at' => 'datetime',
    ];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPlanned(): bool
    {
        return $this->status === 'planned';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'planned' && $this->due_at && $this->due_at->isPast();
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'call' => 'fa-phone',
            'email' => 'fa-envelope',
            'meeting' => 'fa-handshake',
            'note' => 'fa-note-sticky',
            default => 'fa-list-check',
        };
    }
}
