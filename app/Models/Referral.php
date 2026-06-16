<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_account_id', 'referred_client_id', 'referred_email', 'code_used',
        'status', 'qualifying_project_id', 'signup_rewarded_at', 'payment_rewarded_at',
    ];

    protected $casts = [
        'signup_rewarded_at' => 'datetime',
        'payment_rewarded_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(PointsAccount::class, 'referrer_account_id');
    }

    public function referredClient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_client_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'qualifying_project_id');
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'rewarded' => 'success',
            'qualified' => 'info',
            'signed_up' => 'primary',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
