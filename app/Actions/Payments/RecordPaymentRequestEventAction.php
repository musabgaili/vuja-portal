<?php

namespace App\Actions\Payments;

use App\Models\PaymentAttempt;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestEvent;
use Illuminate\Support\Arr;

class RecordPaymentRequestEventAction
{
    public function execute(
        PaymentRequest $paymentRequest,
        string $eventType,
        string $source = 'portal',
        ?array $payload = null,
        ?string $providerEventId = null,
        ?PaymentAttempt $attempt = null,
        ?string $outcome = null,
        mixed $providerOccurredAt = null,
        bool $processed = true,
    ): PaymentRequestEvent {
        $attributes = $providerEventId
            ? ['provider_event_id' => $providerEventId]
            : [
                'payment_request_id' => $paymentRequest->id,
                'event_type' => $eventType,
                'received_at' => now(),
            ];

        $values = [
            'payment_request_id' => $paymentRequest->id,
            'payment_attempt_id' => $attempt?->id,
            'source' => $source,
            'event_type' => $eventType,
            'provider_occurred_at' => $providerOccurredAt,
            'received_at' => now(),
            'processed_at' => $processed ? now() : null,
            'outcome' => $outcome,
            'payload' => $this->safePayload($payload),
        ];

        return $providerEventId
            ? PaymentRequestEvent::firstOrCreate($attributes, $values)
            : PaymentRequestEvent::create($values);
    }

    private function safePayload(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        Arr::forget($payload, ['secret_token', 'data.source.number']);

        return $payload;
    }
}
