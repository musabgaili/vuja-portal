<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    protected $fillable = [
        'points_account_id', 'redemption_option_id', 'cost_points', 'value_meta',
        'voucher_code', 'status', 'applied_to_quote_id', 'applied_at', 'expires_at',
    ];

    protected $casts = [
        'cost_points' => 'integer',
        'value_meta' => 'array',
        'applied_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(PointsAccount::class, 'points_account_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(RedemptionOption::class, 'redemption_option_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'applied_to_quote_id');
    }

    public function meta(string $key, $default = null)
    {
        return data_get($this->value_meta, $key, $default);
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function isApplied(): bool
    {
        return $this->status === 'applied';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->expires_at && $this->expires_at->isPast() && $this->status === 'issued');
    }
}
