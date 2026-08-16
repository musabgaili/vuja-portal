<?php

namespace App\Services\Payments;

use App\Actions\Payments\RecordPaymentRequestEventAction;
use App\Models\PaymentAttempt;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentStatusSynchronizer
{
    public function __construct(
        private RecordPaymentRequestEventAction $recordEvent,
        private PaymentRequestNotificationService $notifications,
    ) {}

    public function sync(PaymentRequest $paymentRequest, array $payment, string $source): PaymentRequest
    {
        $becamePaid = false;

        $result = DB::transaction(function () use ($paymentRequest, $payment, $source, &$becamePaid) {
            $locked = PaymentRequest::query()->lockForUpdate()->findOrFail($paymentRequest->id);
            $this->verify($locked, $payment);

            $safePayment = $payment;
            unset($safePayment['source']['number']);

            $attempt = PaymentAttempt::updateOrCreate(
                ['moyasar_payment_id' => $payment['id']],
                [
                    'payment_request_id' => $locked->id,
                    'status' => $payment['status'],
                    'amount_minor' => $payment['amount'],
                    'currency' => $payment['currency'],
                    'provider_created_at' => $payment['created_at'] ?? null,
                    'provider_updated_at' => $payment['updated_at'] ?? null,
                    'provider_data' => $safePayment,
                ],
            );

            $nextStatus = $this->requestStatus($payment['status']);
            $previousStatus = $locked->status;

            if ($nextStatus && $this->canTransition($previousStatus, $nextStatus)) {
                $locked->status = $nextStatus;
                if ($nextStatus === 'paid' && ! $locked->paid_at) {
                    $locked->paid_at = now();
                    $becamePaid = true;
                }
                $locked->save();
            }

            if ($locked->status !== $previousStatus) {
                $this->recordEvent->execute(
                    $locked,
                    'status_changed',
                    $source,
                    ['from' => $previousStatus, 'to' => $locked->status],
                    attempt: $attempt,
                    outcome: $locked->status,
                );
            }

            return $locked->fresh(['attempts', 'events', 'payable']);
        });

        if ($becamePaid) {
            $this->notifications->sendConfirmation($result);
        }

        return $result;
    }

    private function verify(PaymentRequest $request, array $payment): void
    {
        $metadataUuid = $payment['metadata']['payment_request_uuid'] ?? null;
        $valid = isset($payment['id'], $payment['status'], $payment['amount'], $payment['currency'])
            && (int) $payment['amount'] === $request->total_amount_minor
            && strtoupper((string) $payment['currency']) === $request->currency
            && hash_equals($request->uuid, (string) $metadataUuid);

        if (! $valid) {
            throw ValidationException::withMessages([
                'payment' => __('portal.payments.verification_failed'),
            ]);
        }
    }

    private function requestStatus(string $providerStatus): ?string
    {
        return match ($providerStatus) {
            'paid', 'captured' => 'paid',
            'failed' => 'failed',
            'authorized' => 'authorized',
            'refunded' => 'refunded',
            'voided' => 'voided',
            default => null,
        };
    }

    private function canTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return false;
        }

        if (in_array($from, ['refunded', 'voided', 'cancelled'], true)) {
            return false;
        }

        if ($from === 'paid') {
            return in_array($to, ['refunded', 'voided'], true);
        }

        return true;
    }
}
