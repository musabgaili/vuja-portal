<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PaymentRequest extends Model
{
    use HasFactory, HasUuidRouteKey;

    protected $fillable = [
        'uuid', 'client_id', 'created_by', 'payable_type', 'payable_id',
        'name', 'email', 'phone', 'title', 'description', 'quantity',
        'unit_amount_minor', 'total_amount_minor', 'currency', 'tax_id',
        'billing_address', 'status', 'expires_at', 'sent_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_amount_minor' => 'integer',
            'total_amount_minor' => 'integer',
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentRequestEvent::class)->latest('received_at');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPayable(): bool
    {
        return ! $this->isExpired() && ! in_array($this->status, ['paid', 'cancelled'], true);
    }

    public function amount(): string
    {
        return number_format($this->total_amount_minor / 100, 2);
    }

    public function displayStatus(): string
    {
        return $this->isExpired() && ! in_array($this->status, ['paid', 'refunded', 'voided', 'cancelled'], true)
            ? 'expired'
            : $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->displayStatus()) {
            'paid' => 'success',
            'failed', 'expired', 'voided' => 'danger',
            'cancelled', 'refunded' => 'secondary',
            'authorized' => 'primary',
            'opened' => 'info',
            default => 'warning',
        };
    }
}
