<?php

namespace App\Http\Controllers;

use App\Actions\Payments\RecordPaymentRequestEventAction;
use App\Models\PaymentAttempt;
use App\Models\PaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicPaymentAttemptController extends Controller
{
    public function store(
        Request $request,
        PaymentRequest $paymentRequest,
        RecordPaymentRequestEventAction $recordEvent,
    ): JsonResponse {
        abort_unless($paymentRequest->isPayable(), 410);
        abort_unless(filled($paymentRequest->tax_id) && filled($paymentRequest->billing_address), 422);

        $data = $request->validate([
            'id' => ['required', 'uuid'],
            'status' => ['required', 'string', 'max:24'],
            'amount' => ['required', 'integer'],
            'currency' => ['required', 'string', 'size:3'],
            'created_at' => ['nullable', 'date'],
        ]);

        abort_unless(
            (int) $data['amount'] === $paymentRequest->total_amount_minor
                && strtoupper($data['currency']) === $paymentRequest->currency,
            422,
        );

        DB::transaction(function () use ($data, $paymentRequest, $recordEvent) {
            $attempt = PaymentAttempt::firstOrCreate(
                ['moyasar_payment_id' => $data['id']],
                [
                    'payment_request_id' => $paymentRequest->id,
                    'status' => $data['status'],
                    'amount_minor' => $data['amount'],
                    'currency' => strtoupper($data['currency']),
                    'provider_created_at' => $data['created_at'] ?? null,
                ],
            );

            abort_unless($attempt->payment_request_id === $paymentRequest->id, 409);

            $recordEvent->execute(
                $paymentRequest,
                'attempt_created',
                payload: ['moyasar_payment_id' => $attempt->moyasar_payment_id],
                attempt: $attempt,
                outcome: $attempt->status,
            );
        });

        return response()->json(['saved' => true]);
    }
}
