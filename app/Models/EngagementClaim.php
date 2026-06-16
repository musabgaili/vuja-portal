<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementClaim extends Model
{
    protected $fillable = [
        'points_account_id', 'earning_rule_key', 'platform', 'post_url', 'screenshot_path',
        'posted_at', 'status', 'review_notes', 'points_transaction_id', 'reviewed_by',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(PointsAccount::class, 'points_account_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PointsTransaction::class, 'points_transaction_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function rule(): ?EarningRule
    {
        return EarningRule::byKey($this->earning_rule_key);
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
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
