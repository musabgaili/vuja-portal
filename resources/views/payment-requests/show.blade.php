@extends('layouts.internal-dashboard')
@section('title', $paymentRequest->title)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('payment-requests.index') }}">{{ __('portal.payments.title') }}</a></li>
<li class="breadcrumb-item active">{{ $paymentRequest->title }}</li>
@endsection

@push('styles')
<style>
    .paydesk { --ink: var(--gray-900, #142b4a); --muted: var(--gray-600, #607086); --line: var(--gray-200, #d9e2eb); --teal: #1B565E; color: var(--ink); }
    .pay-hero { display:flex; justify-content:space-between; gap:1rem; align-items:flex-start; flex-wrap:wrap; margin-bottom:1rem; }
    .paydesk-kicker { font-size:.72rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color: var(--teal); }
    .pay-hero h2 { margin:.2rem 0 .25rem; font-size:1.35rem; font-weight:800; }
    .pay-hero p { margin:0; color: var(--muted); }
    .pay-chip { display:inline-flex; margin-top:.45rem; padding:.2rem .55rem; border-radius:999px; background: color-mix(in srgb, var(--teal) 10%, transparent); color: var(--teal); font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.75rem; font-weight:700; }
    .pay-sheet { border:1px solid var(--line); border-radius:16px; background: var(--bg-primary, #fff); overflow:hidden; margin-bottom:1rem; }
    .pay-sheet h3 { margin:0; padding:.9rem 1.1rem; border-bottom:1px solid var(--line); font-size:.9rem; font-weight:800; }
    .pay-sheet .body { padding:1.1rem; }
    .pay-kv { width:100%; }
    .pay-kv td { padding:.45rem 0; border-bottom:1px solid var(--line); }
    .pay-kv tr:last-child td { border-bottom:0; }
    .pay-amount { font-size:1.8rem; font-weight:900; font-variant-numeric: tabular-nums; color: var(--teal); }
</style>
@endpush

@section('content')
<div class="paydesk">
    <div class="pay-hero">
        <div>
            <div class="paydesk-kicker">{{ __('portal.payments.details') }}</div>
            <h2>{{ $paymentRequest->title }}</h2>
            <p>{{ $paymentRequest->name }} · {{ $paymentRequest->email }}</p>
            @if($paymentRequest->displayedQuoteNumber())
                <span class="pay-chip">{{ $paymentRequest->displayedQuoteNumber() }}</span>
            @endif
        </div>
        <span class="badge bg-{{ $paymentRequest->statusColor() }}" style="font-size:.9rem;">{{ $paymentRequest->statusLabel() }}</span>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="pay-sheet">
                <h3>{{ __('portal.payments.details') }}</h3>
                <div class="body">
                    <div class="pay-amount mb-3">{{ $paymentRequest->amount() }} <small class="fs-6 fw-bold">{{ $paymentRequest->currency }}</small></div>
                    <table class="pay-kv">
                        <tr><td>{{ __('portal.payments.phone') }}</td><td class="text-end">{{ $paymentRequest->phone ?: '—' }}</td></tr>
                        <tr><td>{{ __('portal.payments.quantity') }}</td><td class="text-end">{{ $paymentRequest->quantity }} × {{ number_format($paymentRequest->unit_amount_minor / 100, 2) }} {{ $paymentRequest->currency }}</td></tr>
                        @if($paymentRequest->displayedQuoteNumber())
                            <tr><td>{{ __('portal.payments.quote_number') }}</td><td class="text-end">{{ $paymentRequest->displayedQuoteNumber() }}</td></tr>
                        @endif
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

            <div class="pay-sheet">
                <h3>{{ __('portal.payments.timeline') }}</h3>
                <div class="body">
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
            <div class="pay-sheet">
                <h3>{{ __('portal.payments.actions') }}</h3>
                <div class="body">
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

                    @if($paymentRequest->hasQuoteFile())
                        <a href="{{ route('payment-requests.quote', $paymentRequest) }}" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-file-arrow-down me-1"></i>{{ __('portal.payments.download_quote') }}
                        </a>
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
