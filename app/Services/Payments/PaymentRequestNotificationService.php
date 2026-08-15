<?php

namespace App\Services\Payments;

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
            Mail::to($paymentRequest->email)->send(new PaymentRequestMail(
                __('portal.payments.email.subject', ['title' => $paymentRequest->localizedTitle()]),
                __('portal.payments.email.heading'),
                __('portal.payments.email.body', [
                    'name' => $paymentRequest->name,
                    'amount' => $paymentRequest->amount().' '.$paymentRequest->currency,
                    'expires' => $paymentRequest->expires_at->translatedFormat('M j, Y g:i A').' UTC',
                ]),
                $url,
                __('portal.payments.email.action'),
                app()->getLocale() === 'ar' ? 'ar' : 'en',
            ));

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
}
