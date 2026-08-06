<?php

namespace App\Services\Payments;

use App\Mail\GenericNotification;
use App\Models\PaymentRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentRequestNotificationService
{
    public function send(PaymentRequest $paymentRequest, string $url): void
    {
        try {
            $mail = new GenericNotification(
                __('portal.payments.email.subject', ['title' => $paymentRequest->title]),
                __('portal.payments.email.heading'),
                __('portal.payments.email.body', [
                    'name' => $paymentRequest->name,
                    'amount' => $paymentRequest->amount().' '.$paymentRequest->currency,
                    'expires' => $paymentRequest->expires_at->translatedFormat('M j, Y g:i A').' UTC',
                ]),
                $url,
                __('portal.payments.email.action'),
                app()->getLocale() === 'ar' ? 'ar' : 'en',
            );
            $mail->afterCommit();
            Mail::to($paymentRequest->email)->queue($mail);

            if ($paymentRequest->client_id) {
                Cache::forget('notif_feed:'.$paymentRequest->client_id);
            }
        } catch (\Throwable $e) {
            Log::warning('Payment request email could not be queued', [
                'payment_request_id' => $paymentRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
