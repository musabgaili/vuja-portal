<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PointsTransaction extends Model
{
    protected $fillable = [
        'points_account_id', 'direction', 'source', 'points', 'remaining', 'description',
        'status', 'reference_type', 'reference_id', 'earned_at', 'expires_at',
        'created_by', 'approved_by',
    ];

    protected $casts = [
        'points' => 'integer',
        'remaining' => 'integer',
        'earned_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(PointsAccount::class, 'points_account_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Approved earns with points still to consume (FIFO source for spend/expiry). */
    public function scopeLiveEarns(Builder $q): Builder
    {
        return $q->where('direction', 'earn')->where('status', 'approved')->where('remaining', '>', 0);
    }

    public function isEarn(): bool
    {
        return $this->direction === 'earn';
    }
}
