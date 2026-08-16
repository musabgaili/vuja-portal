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

    public const STATUSES = [
        'pending', 'sent', 'opened', 'authorized', 'paid',
        'failed', 'expired', 'cancelled', 'refunded', 'voided',
    ];

    protected $fillable = [
        'uuid', 'client_id', 'created_by', 'payable_type', 'payable_id',
        'name', 'email', 'phone', 'title', 'title_en', 'title_ar',
        'description', 'description_en', 'description_ar', 'quote_number',
        'quote_file', 'quantity', 'unit_amount_minor', 'total_amount_minor',
        'currency', 'tax_id', 'billing_address', 'status', 'expires_at',
        'sent_at', 'paid_at',
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

    public function quote(): ?Quote
    {
        $this->loadMissing('payable');

        return $this->payable instanceof Quote ? $this->payable : null;
    }

    public function invoice(): ?Invoice
    {
        $this->loadMissing('payable');

        return $this->payable instanceof Invoice ? $this->payable : null;
    }

    public function displayedQuoteNumber(): ?string
    {
        $number = trim((string) ($this->quote_number ?: $this->quote()?->quote_number));

        return $number !== '' ? $number : null;
    }

    public function hasQuoteFile(): bool
    {
        return filled($this->quote_file);
    }

    public function hasQuoteDownload(): bool
    {
        return $this->hasQuoteFile() || $this->quote() !== null;
    }

    public function quoteFileName(): string
    {
        $extension = pathinfo((string) $this->quote_file, PATHINFO_EXTENSION) ?: 'pdf';

        return ($this->displayedQuoteNumber() ?: 'quotation').'.'.$extension;
    }

    public function localizedTitle(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $value = $locale === 'ar'
            ? ($this->title_ar ?: $this->title_en ?: $this->title)
            : ($this->title_en ?: $this->title_ar ?: $this->title);

        return (string) $value;
    }

    public function localizedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $value = $locale === 'ar'
            ? ($this->description_ar ?: $this->description_en ?: $this->description)
            : ($this->description_en ?: $this->description_ar ?: $this->description);

        return filled($value) ? (string) $value : null;
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

    public function statusLabel(): string
    {
        return __('portal.payments.status.'.$this->displayStatus());
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
