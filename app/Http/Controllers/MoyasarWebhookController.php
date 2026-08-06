<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMoyasarWebhook;
use App\Models\PaymentAttempt;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class MoyasarWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = $request->json()->all();
        $expected = (string) config('services.moyasar.webhook_secret');
        $provided = (string) ($payload['secret_token'] ?? '');

        abort_unless($expected !== '' && hash_equals($expected, $provided), 401);

        $eventId = $payload['id'] ?? null;
        $eventType = $payload['type'] ?? null;
        abort_unless(is_string($eventId) && is_string($eventType), 422);

        $paymentId = data_get($payload, 'data.id');
        $requestUuid = data_get($payload, 'data.metadata.payment_request_uuid');
        $paymentRequest = is_string($requestUuid)
            ? PaymentRequest::where('uuid', $requestUuid)->first()
            : null;

        if (! $paymentRequest && is_string($paymentId)) {
            $paymentRequest = PaymentAttempt::with('paymentRequest')
                ->where('moyasar_payment_id', $paymentId)
                ->first()?->paymentRequest;
        }

        $safePayload = $payload;
        Arr::forget($safePayload, ['secret_token', 'data.source.number']);

        $event = PaymentRequestEvent::firstOrCreate(
            ['provider_event_id' => $eventId],
            [
                'payment_request_id' => $paymentRequest?->id,
                'source' => 'webhook',
                'event_type' => $eventType,
                'provider_occurred_at' => $payload['created_at'] ?? null,
                'received_at' => now(),
                'payload' => $safePayload,
            ],
        );

        if (! $event->processed_at) {
            ProcessMoyasarWebhook::dispatch($event->id);
        }

        return response()->noContent();
    }
}
