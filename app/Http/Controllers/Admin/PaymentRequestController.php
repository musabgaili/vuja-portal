<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Payments\CreatePaymentRequestAction;
use App\Actions\Payments\SendPaymentRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymentRequestRequest;
use App\Models\PaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentRequestController extends Controller
{
    public function index(Request $request): View
    {
        $sort = in_array($request->input('sort'), ['status', 'date'], true)
            ? $request->input('sort')
            : 'date';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $status = $request->input('status');
        $status = is_string($status) && in_array($status, PaymentRequest::STATUSES, true) ? $status : '';

        $query = PaymentRequest::query()->with(['client', 'creator', 'payable']);

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%'.$term.'%')
                    ->orWhere('email', 'like', '%'.$term.'%')
                    ->orWhere('quote_number', 'like', '%'.$term.'%');
            });
        }

        if ($status !== '') {
            if ($status === 'expired') {
                $query->where('expires_at', '<', now())
                    ->whereNotIn('status', ['paid', 'refunded', 'voided', 'cancelled']);
            } else {
                $query->where('status', $status);
                if (! in_array($status, ['paid', 'refunded', 'voided', 'cancelled'], true)) {
                    $query->where('expires_at', '>=', now());
                }
            }
        }

        if ($sort === 'status') {
            $query->orderByRaw(
                "CASE WHEN expires_at < ? AND status NOT IN ('paid','refunded','voided','cancelled') THEN 'expired' ELSE status END {$direction}",
                [now()],
            )->orderByDesc('created_at');
        } else {
            $query->orderBy('created_at', $direction);
        }

        return view('payment-requests.index', [
            'requests' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'q' => (string) $request->input('q', ''),
                'status' => $status,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(): View
    {
        return view('payment-requests.create');
    }

    public function show(PaymentRequest $paymentRequest): View
    {
        $paymentRequest->load(['client', 'creator', 'attempts', 'events.attempt', 'payable']);

        return view('payment-requests.show', [
            'paymentRequest' => $paymentRequest,
            'publicUrl' => $paymentRequest->expires_at->isFuture()
                ? SendPaymentRequestAction::publicUrl($paymentRequest)
                : null,
        ]);
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

    public function quote(PaymentRequest $paymentRequest): StreamedResponse
    {
        abort_unless(
            $paymentRequest->hasQuoteFile() && Storage::disk('private')->exists($paymentRequest->quote_file),
            404,
            __('portal.payments.quote_missing'),
        );

        return Storage::disk('private')->download($paymentRequest->quote_file, $paymentRequest->quoteFileName());
    }
}
