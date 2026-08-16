<?php

namespace App\Services\Payments;

use App\Mail\PaymentConfirmationMail;
use App\Mail\PaymentRequestMail;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentRequestNotificationService
{
    public function send(PaymentRequest $paymentRequest, string $url): void
    {
        try {
            Mail::to($paymentRequest->email)->send(new PaymentRequestMail($paymentRequest, $url));

            if ($paymentRequest->client_id) {
                Cache::forget('notif_feed:'.$paymentRequest->client_id);
            }
        } catch (\Throwable $e) {
            Log::warning('Payment request email could not be sent', [
                'payment_request_id' => $paymentRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function sendConfirmation(PaymentRequest $paymentRequest): void
    {
        try {
            Mail::to($paymentRequest->email)->send(new PaymentConfirmationMail($paymentRequest));

            if ($paymentRequest->client_id) {
                Cache::forget('notif_feed:'.$paymentRequest->client_id);
            }
        } catch (\Throwable $e) {
            Log::warning('Payment confirmation email could not be sent', [
                'payment_request_id' => $paymentRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
