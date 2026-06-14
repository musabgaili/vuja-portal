<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'opportunity_id', 'company_id', 'contact_id', 'client_id', 'created_by', 'project_id',
        'title', 'scope', 'status', 'total_internal', 'total_client', 'valid_until',
        'accepted_signature', 'accepted_at', 'accepted_ip', 'reject_reason',
    ];

    protected $casts = [
        'total_internal' => 'decimal:2',
        'total_client' => 'decimal:2',
        'valid_until' => 'date',
        'accepted_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** Client-facing pricing: grouped by category (item prices hidden). */
    public function clientGrouped(): array
    {
        return $this->items->groupBy('category')
            ->map(fn ($g) => (float) $g->sum('line_client'))
            ->toArray();
    }

    public function margin(): float
    {
        return (float) $this->total_client - (float) $this->total_internal;
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'accepted' => 'success',
            'sent' => 'info',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
