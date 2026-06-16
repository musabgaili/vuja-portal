<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointGrantRequest extends Model
{
    protected $fillable = [
        'client_id', 'points', 'reason', 'status', 'suggested_by', 'approved_by', 'points_transaction_id',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function suggestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PointsTransaction::class, 'points_transaction_id');
    }

    public function isSuggested(): bool
    {
        return $this->status === 'suggested';
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }
}
