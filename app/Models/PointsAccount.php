<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PointsAccount extends Model
{
    protected $fillable = [
        'client_id', 'balance', 'lifetime_points', 'tier_id', 'referral_code',
    ];

    protected $casts = [
        'balance' => 'integer',
        'lifetime_points' => 'integer',
    ];

    protected static function booted(): void
    {
        // Every account gets a unique share code the moment it is created.
        static::creating(function (PointsAccount $account) {
            if (empty($account->referral_code)) {
                $account->referral_code = static::generateReferralCode();
            }
        });
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = 'VJ'.strtoupper(Str::random(6));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function referralLink(): string
    {
        return rtrim(config('engagement_points.referral_base_url'), '/').'/'.$this->referral_code;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PointsTransaction::class)->latest('id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class)->latest('id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_account_id')->latest('id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(EngagementClaim::class)->latest('id');
    }
}
