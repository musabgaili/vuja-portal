<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Payments\CreatePaymentRequestAction;
use App\Actions\Payments\SendPaymentRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequestRequest;
use App\Models\PaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentRequestController extends Controller
{
    public function index(): View
    {
        return $this->view();
    }

    public function show(PaymentRequest $paymentRequest): View
    {
        return $this->view($paymentRequest->load(['client', 'creator', 'attempts', 'events.attempt']));
    }

    public function store(
        CreatePaymentRequestRequest $request,
        CreatePaymentRequestAction $create,
        SendPaymentRequestAction $send,
    ): RedirectResponse {
        $paymentRequest = $create->execute($request->validated(), $request->user());

        if ($request->boolean('send')) {
            $paymentRequest = $send->execute($paymentRequest);
        }

        return redirect()
            ->route('payment-requests.show', $paymentRequest)
            ->with('success', $request->boolean('send')
                ? __('portal.payments.saved_sent')
                : __('portal.payments.saved'));
    }

    public function send(PaymentRequest $paymentRequest, SendPaymentRequestAction $send): RedirectResponse
    {
        $send->execute($paymentRequest);

        return back()->with('success', __('portal.payments.sent'));
    }

    private function view(?PaymentRequest $selected = null): View
    {
        $requests = PaymentRequest::query()
            ->with(['client', 'creator'])
            ->latest()
            ->paginate(15);

        return view('payment-requests.index', [
            'requests' => $requests,
            'selected' => $selected,
            'publicUrl' => $selected ? SendPaymentRequestAction::publicUrl($selected) : null,
        ]);
    }
}
