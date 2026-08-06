<?php

namespace App\Http\Controllers;

use App\Actions\Payments\RecordPaymentRequestEventAction;
use App\Actions\Payments\SendPaymentRequestAction;
use App\Http\Requests\UpdatePaymentBillingRequest;
use App\Models\PaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicPaymentRequestController extends Controller
{
    public function show(
        Request $request,
        PaymentRequest $paymentRequest,
        RecordPaymentRequestEventAction $recordEvent,
    ): View {
        $this->guardPayable($paymentRequest);

        DB::transaction(function () use ($request, $paymentRequest, $recordEvent) {
            $locked = PaymentRequest::query()->lockForUpdate()->findOrFail($paymentRequest->id);

            if (in_array($locked->status, ['pending', 'sent'], true)) {
                $locked->update(['status' => 'opened']);
            }

            $recordEvent->execute($locked, 'opened', payload: [
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
            ], outcome: 'viewed');
        });

        return view('payment-requests.public', [
            'paymentRequest' => $paymentRequest->fresh(['attempts']),
            'paymentReady' => filled($paymentRequest->tax_id) && filled($paymentRequest->billing_address),
            'result' => null,
        ]);
    }

    public function billing(
        UpdatePaymentBillingRequest $request,
        PaymentRequest $paymentRequest,
        RecordPaymentRequestEventAction $recordEvent,
    ): RedirectResponse {
        $this->guardPayable($paymentRequest);

        DB::transaction(function () use ($request, $paymentRequest, $recordEvent) {
            $locked = PaymentRequest::query()->lockForUpdate()->findOrFail($paymentRequest->id);
            $locked->update($request->validated());
            $recordEvent->execute($locked, 'billing_details_saved', outcome: 'saved');
        });

        return redirect(SendPaymentRequestAction::publicUrl($paymentRequest))
            ->with('success', __('portal.payments.billing_saved'));
    }

    private function guardPayable(PaymentRequest $paymentRequest): void
    {
        abort_if($paymentRequest->isExpired(), 410, __('portal.payments.expired'));
        abort_if(in_array($paymentRequest->status, ['cancelled', 'refunded', 'voided'], true), 410);
    }
}
