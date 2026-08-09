<?php

namespace App\Http\Controllers;

use App\Actions\Payments\RecordPaymentRequestEventAction;
use App\Actions\Payments\SendPaymentRequestAction;
use App\Models\PaymentRequest;
use App\Services\Payments\MoyasarClient;
use App\Services\Payments\PaymentStatusSynchronizer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoyasarCallbackController extends Controller
{
    public function __invoke(
        Request $request,
        PaymentRequest $paymentRequest,
        MoyasarClient $moyasar,
        PaymentStatusSynchronizer $synchronizer,
        RecordPaymentRequestEventAction $recordEvent,
    ): View {
        $paymentId = $request->string('id')->toString();
        abort_unless(preg_match('/^[0-9a-f-]{36}$/i', $paymentId), 422);

        try {
            $payment = $moyasar->fetchPayment($paymentId);
            $paymentRequest = $synchronizer->sync($paymentRequest, $payment, 'callback');
            $result = $paymentRequest->status === 'paid' ? 'paid' : 'pending';

            $recordEvent->execute(
                $paymentRequest,
                'callback_received',
                'callback',
                ['moyasar_payment_id' => $paymentId],
                attempt: $paymentRequest->attempts->firstWhere('moyasar_payment_id', $paymentId),
                outcome: $paymentRequest->status,
            );
        } catch (\Throwable $e) {
            report($e);
            $result = 'failed';
            $recordEvent->execute(
                $paymentRequest,
                'callback_rejected',
                'callback',
                ['moyasar_payment_id' => $paymentId],
                outcome: 'rejected',
            );
        }

        $paymentRequest = $paymentRequest->fresh(['attempts', 'payable.items']);

        return view('payment-requests.public', [
            'paymentRequest' => $paymentRequest,
            'quote' => $paymentRequest->quote(),
            'quoteDownloadUrl' => $paymentRequest->quote() ? SendPaymentRequestAction::quoteDownloadUrl($paymentRequest) : null,
            'paymentReady' => filled($paymentRequest->tax_id) && filled($paymentRequest->billing_address),
            'result' => $result,
        ]);
    }
}
