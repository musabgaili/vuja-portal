@extends('layouts.internal-dashboard')
@section('title', $paymentRequest->title)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('payment-requests.index') }}">{{ __('portal.payments.title') }}</a></li>
<li class="breadcrumb-item active">{{ $paymentRequest->title }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-credit-card"></i> {{ $paymentRequest->title }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ $paymentRequest->name }} · {{ $paymentRequest->email }}</p>
    </div>
    <span class="badge bg-{{ $paymentRequest->statusColor() }}" style="font-size:.9rem;">{{ $paymentRequest->statusLabel() }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">{{ __('portal.payments.details') }}</span></div>
            <div class="card-content" style="overflow-x:auto;">
                <table class="table mb-0">
                    <tr><td>{{ __('portal.payments.recipient') }}</td><td class="text-end">{{ $paymentRequest->name }}</td></tr>
                    <tr><td>{{ __('portal.payments.email') }}</td><td class="text-end">{{ $paymentRequest->email }}</td></tr>
                    <tr><td>{{ __('portal.payments.phone') }}</td><td class="text-end">{{ $paymentRequest->phone ?: '—' }}</td></tr>
                    @if($paymentRequest->quote())
                        <tr>
                            <td>{{ __('portal.payments.quote_number') }}</td>
                            <td class="text-end">{{ $paymentRequest->quote()->quote_number ?: '#'.$paymentRequest->quote()->id }}</td>
                        </tr>
                    @endif
                    <tr><td>{{ __('portal.payments.quantity') }}</td><td class="text-end">{{ $paymentRequest->quantity }}</td></tr>
                    <tr><td>{{ __('portal.payments.unit_amount') }}</td><td class="text-end">{{ number_format($paymentRequest->unit_amount_minor / 100, 2) }} {{ $paymentRequest->currency }}</td></tr>
                    <tr><td>{{ __('portal.payments.total') }}</td><td class="text-end"><strong>{{ $paymentRequest->amount() }} {{ $paymentRequest->currency }}</strong></td></tr>
                    <tr><td>{{ __('portal.payments.expires') }}</td><td class="text-end">{{ $paymentRequest->expires_at->translatedFormat('M j, Y g:i A') }} UTC</td></tr>
                    <tr><td>{{ __('portal.payments.created_by') }}</td><td class="text-end">{{ $paymentRequest->creator?->name ?: '—' }} · {{ $paymentRequest->created_at->translatedFormat('M j, Y g:i A') }}</td></tr>
                    @if($paymentRequest->tax_id)
                        <tr><td>{{ __('portal.payments.tax_id') }}</td><td class="text-end">{{ $paymentRequest->tax_id }}</td></tr>
                    @endif
                    @if($paymentRequest->billing_address)
                        <tr><td>{{ __('portal.payments.address') }}</td><td class="text-end">{{ $paymentRequest->billing_address }}</td></tr>
                    @endif
                </table>
                @if($paymentRequest->description)
                    <p class="text-muted mt-3 mb-0">{{ $paymentRequest->description }}</p>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.payments.timeline') }}</span></div>
            <div class="card-content">
                @forelse($paymentRequest->events as $event)
                    <div class="d-flex justify-content-between gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <strong>{{ str($event->event_type)->replace('_', ' ')->title() }}</strong>
                            <div class="small text-muted">{{ $event->source }}{{ $event->outcome ? ' · '.$event->outcome : '' }}</div>
                        </div>
                        <small class="text-muted text-nowrap">{{ $event->received_at->translatedFormat('M j, Y · g:i:s A') }} UTC</small>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('portal.payments.empty') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.payments.actions') }}</span></div>
            <div class="card-content">
                @if($publicUrl)
                    <label class="form-label small fw-bold" for="payment-public-url">{{ __('portal.payments.copy_link') }}</label>
                    <div class="input-group mb-3">
                        <input id="payment-public-url" class="form-control" value="{{ $publicUrl }}" readonly aria-label="{{ __('portal.payments.copy_link') }}">
                        <button type="button" class="btn btn-dark" id="copy-payment-link"><i class="fa-regular fa-copy"></i></button>
                    </div>
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-arrow-up-right-from-square me-1"></i>{{ __('portal.payments.open_link') }}
                    </a>
                @else
                    <div class="alert alert-warning">{{ __('portal.payments.expired') }}</div>
                @endif

                @if($paymentRequest->isPayable())
                    <form action="{{ route('payment-requests.send', $paymentRequest) }}" method="POST">
                        @csrf
                        <button class="btn btn-outline-secondary w-100"><i class="fa-regular fa-envelope me-1"></i>{{ __('portal.payments.send_again') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('copy-payment-link')?.addEventListener('click', async () => {
    const input = document.getElementById('payment-public-url');
    try {
        await navigator.clipboard.writeText(input.value);
    } catch (_) {
        input.select();
        document.execCommand('copy');
    }
    window.showAppToast?.(@json(__('portal.payments.link_copied')), 'success');
});
</script>
@endpush
