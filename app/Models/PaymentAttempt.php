<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentAttempt extends Model
{
    protected $fillable = [
        'payment_request_id', 'moyasar_payment_id', 'status', 'amount_minor',
        'currency', 'provider_created_at', 'provider_updated_at', 'provider_data',
    ];

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'provider_created_at' => 'datetime',
            'provider_updated_at' => 'datetime',
            'provider_data' => 'array',
        ];
    }

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentRequestEvent::class);
    }
}
