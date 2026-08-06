<?php

namespace App\Jobs;

use App\Models\PaymentRequestEvent;
use App\Services\Payments\MoyasarClient;
use App\Services\Payments\PaymentStatusSynchronizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessMoyasarWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public array $backoff = [10, 30, 90, 180];

    public function __construct(public int $eventId) {}

    public function handle(MoyasarClient $moyasar, PaymentStatusSynchronizer $synchronizer): void
    {
        $event = PaymentRequestEvent::with('paymentRequest')->findOrFail($this->eventId);

        if ($event->processed_at) {
            return;
        }

        if (! $event->paymentRequest) {
            $event->update(['processed_at' => now(), 'outcome' => 'unmatched']);

            return;
        }

        if (! str_starts_with($event->event_type, 'payment_')) {
            $event->update(['processed_at' => now(), 'outcome' => 'logged']);

            return;
        }

        $paymentId = data_get($event->payload, 'data.id');
        if (! is_string($paymentId)) {
            $event->update(['processed_at' => now(), 'outcome' => 'invalid_payload']);

            return;
        }

        $payment = $moyasar->fetchPayment($paymentId);
        $request = $synchronizer->sync($event->paymentRequest, $payment, 'webhook');
        $event->update([
            'payment_attempt_id' => $request->attempts()->where('moyasar_payment_id', $paymentId)->value('id'),
            'processed_at' => now(),
            'outcome' => $request->status,
        ]);
    }
}
