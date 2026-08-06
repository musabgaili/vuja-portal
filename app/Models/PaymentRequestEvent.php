<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentRequestEvent extends Model
{
    protected $fillable = [
        'payment_request_id', 'payment_attempt_id', 'source', 'provider_event_id',
        'event_type', 'provider_occurred_at', 'received_at', 'processed_at',
        'outcome', 'payload',
    ];

    protected function casts(): array
    {
        return [
            'provider_occurred_at' => 'datetime',
            'received_at' => 'datetime',
            'processed_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function paymentRequest(): BelongsTo
    {
        return $this->belongsTo(PaymentRequest::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(PaymentAttempt::class, 'payment_attempt_id');
    }
}
