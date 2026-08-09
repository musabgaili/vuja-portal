<?php

namespace App\Actions\Payments;

use App\Models\PaymentRequest;
use App\Services\Payments\PaymentRequestNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class SendPaymentRequestAction
{
    public function __construct(
        private RecordPaymentRequestEventAction $recordEvent,
        private PaymentRequestNotificationService $notifications,
    ) {}

    public function execute(PaymentRequest $paymentRequest): PaymentRequest
    {
        $sent = DB::transaction(function () use ($paymentRequest) {
            $locked = PaymentRequest::query()->lockForUpdate()->findOrFail($paymentRequest->id);

            abort_if($locked->isExpired(), 422, __('portal.payments.expired'));
            abort_if(in_array($locked->status, ['paid', 'cancelled'], true), 422);

            $locked->update([
                'status' => $locked->status === 'pending' ? 'sent' : $locked->status,
                'sent_at' => now(),
            ]);

            $this->recordEvent->execute($locked, 'sent', outcome: 'queued');

            return $locked;
        });

        $this->notifications->send($sent, self::publicUrl($sent));

        return $sent->fresh();
    }

    public static function publicUrl(PaymentRequest $paymentRequest): string
    {
        return URL::temporarySignedRoute(
            'payments.public.show',
            $paymentRequest->expires_at,
            ['paymentRequest' => $paymentRequest],
        );
    }

    public static function quoteDownloadUrl(PaymentRequest $paymentRequest): string
    {
        return URL::temporarySignedRoute(
            'payments.public.quote',
            $paymentRequest->expires_at,
            ['paymentRequest' => $paymentRequest],
        );
    }
}
