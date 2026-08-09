<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $paymentRequest->title }} · {{ __('portal.payments.brand') }}</title>
    @include('partials.theme-head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/moyasar-payment-form@2.2.10/dist/moyasar.css">
    <style>
        :root { --ink:#142b4a; --blue:#2457d6; --cyan:#22aebf; --mist:#edf4f8; --paper:#fff; --muted:#607086; --line:#d9e2eb; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; color:var(--ink); background:radial-gradient(circle at 8% 0%,rgba(34,174,191,.14),transparent 34%),#f3f6fa; font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif; }
        .pay-shell { width:min(100%,1120px); margin-inline:auto; padding:1rem; }
        .pay-brand { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.4rem .15rem 1rem; }
        .pay-logo { display:flex; align-items:center; gap:.65rem; color:var(--ink); text-decoration:none; }
        .pay-logo img { height:36px; width:auto; }
        .pay-logo strong { font-size:1.05rem; letter-spacing:-.02em; }
        .pay-brand-actions { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; justify-content:flex-end; }
        .secure-pill,.brand-download { display:inline-flex; align-items:center; gap:.4rem; color:#16636d; background:#dff5f5; padding:.45rem .7rem; border-radius:999px; font-size:.72rem; font-weight:800; text-decoration:none; }
        .pay-layout { display:grid; grid-template-columns:1fr; border-radius:22px; overflow:hidden; background:var(--paper); box-shadow:0 24px 70px rgba(20,43,74,.12); }
        .pay-docket { position:relative; padding:1.25rem; background:linear-gradient(155deg,#152f54,#214e90); color:#fff; }
        .pay-docket::after { content:""; position:absolute; inset-inline:16px; bottom:-6px; height:12px; background:radial-gradient(circle,#f3f6fa 5px,transparent 6px) 0 0/18px 12px repeat-x; }
        .docket-label { font-size:.67rem; font-weight:850; letter-spacing:.17em; text-transform:uppercase; color:#8fe2e7; }
        .quote-chip { display:inline-flex; margin:.55rem 0 .2rem; padding:.35rem .65rem; border-radius:999px; background:rgba(111,220,229,.18); color:#d7fbff; font-size:.72rem; font-weight:800; letter-spacing:.06em; }
        .pay-docket h1 { margin:.65rem 0 .45rem; font-size:clamp(1.45rem,6vw,2.35rem); line-height:1.12; letter-spacing:-.03em; }
        .docket-desc { color:#d9e5f6; line-height:1.65; margin-bottom:1.1rem; }
        .quote-table { width:100%; border-collapse:collapse; font-size:.78rem; margin:.35rem 0 .85rem; }
        .quote-table th { color:#9eb4d0; font-size:.62rem; text-transform:uppercase; letter-spacing:.08em; font-weight:750; text-align:start; padding:0 0 .45rem; }
        .quote-table td { padding:.4rem 0; border-top:1px solid rgba(255,255,255,.12); vertical-align:top; }
        .quote-table .num { text-align:end; white-space:nowrap; }
        .quote-table small { display:block; color:#9eb4d0; font-weight:500; }
        .quote-totals { display:grid; gap:.35rem; margin-bottom:.85rem; font-size:.8rem; color:#bed0e8; }
        .quote-totals div { display:flex; justify-content:space-between; gap:1rem; }
        .quote-totals strong { color:#fff; }
        .quote-download { display:inline-flex; align-items:center; justify-content:center; width:100%; gap:.45rem; margin:.35rem 0 1rem; padding:.75rem .9rem; border-radius:11px; background:#fff; color:#152f54; text-decoration:none; font-size:.8rem; font-weight:850; }
        .docket-rule { border:0; border-top:1px dashed rgba(255,255,255,.28); margin:1.1rem 0; }
        .docket-total { display:flex; justify-content:space-between; align-items:end; gap:1rem; }
        .docket-total span { color:#bed0e8; font-size:.75rem; text-transform:uppercase; letter-spacing:.1em; }
        .docket-total strong { display:block; font-size:clamp(1.8rem,8vw,3rem); line-height:1; }
        .docket-meta { display:grid; grid-template-columns:1fr 1fr; gap:.9rem; margin-top:1.4rem; }
        .docket-meta small { display:block; color:#9eb4d0; font-size:.66rem; text-transform:uppercase; letter-spacing:.08em; }
        .docket-meta b { display:block; margin-top:.2rem; font-size:.83rem; word-break:break-word; }
        .expiry-rail { margin-top:1.5rem; border-inline-start:3px solid #6edce5; padding-inline-start:.75rem; }
        .expiry-rail small { color:#b9d2e9; }
        #pay-countdown { font-weight:850; font-variant-numeric:tabular-nums; }
        .pay-stage { padding:1.25rem; }
        .stage-kicker { color:var(--blue); font-size:.7rem; font-weight:850; letter-spacing:.14em; text-transform:uppercase; }
        .pay-stage h2 { margin:.4rem 0 .4rem; font-size:1.35rem; }
        .stage-copy { color:var(--muted); line-height:1.6; margin:0 0 1.1rem; }
        .notice { padding:.9rem 1rem; border-radius:12px; margin-bottom:1rem; font-size:.88rem; }
        .notice-success { color:#0b6646; background:#e1f6ec; border:1px solid #b7ead3; }
        .notice-warning { color:#7b5103; background:#fff4d8; border:1px solid #f2dc9e; }
        .notice-danger { color:#902e37; background:#fde7e9; border:1px solid #f3bdc2; }
        .billing-form { display:grid; gap:.9rem; }
        .billing-form label { display:block; margin-bottom:.35rem; color:#35465b; font-size:.78rem; font-weight:800; }
        .billing-form input,.billing-form textarea { width:100%; border:1px solid var(--line); border-radius:11px; padding:.85rem; font:inherit; background:#fff; color:var(--ink); outline:none; transition:border .18s,box-shadow .18s; }
        .billing-form input:focus,.billing-form textarea:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(36,87,214,.13); }
        .billing-form textarea { min-height:110px; resize:vertical; }
        .field-hint { color:var(--muted); font-size:.72rem; margin-top:.3rem; }
        .field-error { color:#b32636; font-size:.75rem; margin-top:.3rem; }
        .primary-action { width:100%; border:0; border-radius:11px; padding:.9rem 1rem; color:#fff; background:var(--blue); font-weight:850; cursor:pointer; }
        .card-head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.75rem; }
        .card-head h3 { margin:0; font-size:1rem; }
        .card-networks { color:var(--muted); font-size:.72rem; }
        .secure-note { display:flex; align-items:flex-start; gap:.55rem; color:var(--muted); font-size:.72rem; line-height:1.5; margin-top:.8rem; }
        .mysr-form { min-height:210px; }
        .mysr-form .mysr-form-button { background:var(--blue)!important; }
        @media (min-width:720px) {
            .pay-shell { padding:2rem; }
            .pay-layout { grid-template-columns:minmax(300px,.9fr) minmax(390px,1.1fr); min-height:650px; }
            .pay-docket,.pay-stage { padding:clamp(1.7rem,4vw,3rem); }
            .pay-docket::after { inset-block:16px; inset-inline-start:auto; inset-inline-end:-6px; width:12px; height:auto; background:radial-gradient(circle,#f3f6fa 5px,transparent 6px) 0 0/12px 18px repeat-y; }
        }
        @media (prefers-reduced-motion:no-preference) {
            .pay-layout { animation:pay-enter .45s ease-out both; }
            @keyframes pay-enter { from { opacity:0; transform:translateY(10px); } }
        }
    </style>
</head>
<body>
@php
    $logo = file_exists(public_path('images/vd-logo-dark-trimmed.png'))
        ? 'images/vd-logo-dark-trimmed.png'
        : 'images/vd-logo-dark.png';
    $quote = $quote ?? $paymentRequest->quote();
    $quoteNumber = $paymentRequest->displayedQuoteNumber();
    $quoteDownloadUrl = $quoteDownloadUrl ?? ($paymentRequest->hasQuoteDownload() ? \App\Actions\Payments\SendPaymentRequestAction::quoteDownloadUrl($paymentRequest) : null);
    $quoteItems = $quote?->clientVisibleItems() ?? collect();
@endphp
<main class="pay-shell">
    <header class="pay-brand">
        <div class="pay-logo">
            @if(file_exists(public_path($logo)))
                <img src="{{ asset($logo) }}" alt="{{ __('portal.payments.brand') }}">
            @endif
            <strong>{{ __('portal.payments.brand') }}</strong>
        </div>
        <div class="pay-brand-actions">
            @if($quoteDownloadUrl)
                <a class="brand-download" href="{{ $quoteDownloadUrl }}">{{ __('portal.payments.download_quote') }}</a>
            @endif
            <span class="secure-pill">● {{ __('portal.payments.pay_securely') }}</span>
        </div>
    </header>

    <div class="pay-layout">
        <aside class="pay-docket">
            <div class="docket-label">{{ __('portal.payments.pay_securely') }}</div>
            @if($quoteNumber)
                <div class="quote-chip">{{ __('portal.payments.quote_number') }} · {{ $quoteNumber }}</div>
            @endif
            <h1>{{ $paymentRequest->title }}</h1>
            <p class="docket-desc">{{ $paymentRequest->description ?: __('portal.payments.public_intro') }}</p>

            @if($quote || $quoteDownloadUrl)
                <div class="docket-label" style="margin-bottom:.55rem">{{ __('portal.payments.quote_details') }}</div>
                @if($quote && $quoteItems->isNotEmpty())
                    <table class="quote-table">
                        <thead>
                            <tr>
                                <th>{{ __('scope.description') }}</th>
                                <th class="num">{{ __('scope.qty') }}</th>
                                <th class="num">{{ __('scope.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quoteItems as $item)
                                <tr>
                                    <td>{{ $item->description }}@if($item->details)<small>{{ $item->details }}</small>@endif</td>
                                    <td class="num">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                                    <td class="num">{{ number_format((float) $item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="quote-totals">
                        @if((float) $quote->subtotal > 0 || (float) $quote->vat_amount > 0)
                            <div><span>{{ __('scope.subtotal') }}</span><strong>{{ number_format((float) $quote->subtotal, 2) }} {{ $paymentRequest->currency }}</strong></div>
                            <div><span>{{ __('scope.vat', ['rate' => rtrim(rtrim(number_format((float) $quote->vat_rate, 2), '0'), '.')]) }}</span><strong>{{ number_format((float) $quote->vat_amount, 2) }} {{ $paymentRequest->currency }}</strong></div>
                        @endif
                        <div><span>{{ __('portal.payments.quote_total') }}</span><strong>{{ number_format($quote->invoiceTotal(), 2) }} {{ $paymentRequest->currency }}</strong></div>
                    </div>
                @endif
                @if($quoteDownloadUrl)
                    <a class="quote-download" href="{{ $quoteDownloadUrl }}">{{ __('portal.payments.download_quote') }}</a>
                @endif
            @endif

            <hr class="docket-rule">
            <div class="docket-total">
                <div><span>{{ __('portal.payments.amount_due') }}</span><strong>{{ $paymentRequest->amount() }}</strong></div>
                <b>{{ $paymentRequest->currency }}</b>
            </div>
            <div class="docket-meta">
                <div><small>{{ __('portal.payments.recipient') }}</small><b>{{ $paymentRequest->name }}</b></div>
                <div><small>{{ __('portal.payments.quantity') }}</small><b>{{ $paymentRequest->quantity }}</b></div>
                <div><small>{{ __('portal.payments.email') }}</small><b>{{ $paymentRequest->email }}</b></div>
                <div><small>{{ __('portal.payments.unit_amount') }}</small><b>{{ number_format($paymentRequest->unit_amount_minor / 100, 2) }} {{ $paymentRequest->currency }}</b></div>
            </div>
            <div class="expiry-rail">
                <small>{{ __('portal.payments.expires') }}</small>
                <div id="pay-countdown" data-expires="{{ $paymentRequest->expires_at->toIso8601String() }}">—</div>
            </div>
        </aside>

        <section class="pay-stage">
            @if(session('success'))<div class="notice notice-success">{{ session('success') }}</div>@endif
            @if($result === 'paid' || $paymentRequest->status === 'paid')
                <div class="stage-kicker">{{ __('portal.payments.status') }}</div>
                <h2>{{ __('portal.payments.paid') }}</h2>
                <div class="notice notice-success">{{ __('portal.payments.paid_message') }}</div>
            @elseif($result === 'pending')
                <div class="stage-kicker">{{ __('portal.payments.status') }}</div>
                <h2>{{ __('portal.payments.pay_securely') }}</h2>
                <div class="notice notice-warning">{{ __('portal.payments.pending_message') }}</div>
            @elseif($result === 'failed')
                <div class="notice notice-danger">{{ __('portal.payments.failed_message') }}</div>
            @endif

            @if($paymentRequest->isPayable())
                @if(!$paymentReady)
                    <div class="stage-kicker">Step 1 · {{ __('portal.payments.billing') }}</div>
                    <h2>{{ __('portal.payments.billing') }}</h2>
                    <p class="stage-copy">{{ __('portal.payments.public_intro') }}</p>
                    <form class="billing-form" method="POST" action="{{ route('payments.public.billing', $paymentRequest) }}">
                        @csrf
                        <div>
                            <label for="tax-id">{{ __('portal.payments.tax_id') }}</label>
                            <input id="tax-id" name="tax_id" inputmode="numeric" pattern="3[0-9]{13}3" maxlength="15" value="{{ old('tax_id', $paymentRequest->tax_id) }}" required>
                            <div class="field-hint">{{ __('portal.payments.tax_id_hint') }}</div>
                            @error('tax_id')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="billing-address">{{ __('portal.payments.address') }}</label>
                            <textarea id="billing-address" name="billing_address" required>{{ old('billing_address', $paymentRequest->billing_address) }}</textarea>
                            @error('billing_address')<div class="field-error">{{ $message }}</div>@enderror
                        </div>
                        <button class="primary-action" type="submit">{{ __('portal.payments.continue') }}</button>
                    </form>
                @elseif(str_starts_with((string) config('services.moyasar.publishable_key'), 'pk_'))
                    <div class="stage-kicker">Step 2 · {{ __('portal.payments.card') }}</div>
                    <div class="card-head">
                        <h3>{{ __('portal.payments.card') }}</h3>
                        <span class="card-networks">mada · Visa · Mastercard</span>
                    </div>
                    <div class="mysr-form"></div>
                    <div class="secure-note"><span>▣</span><span>{{ __('portal.payments.secure_note') }}</span></div>
                @else
                    <div class="notice notice-warning">{{ __('portal.payments.configuration_missing') }}</div>
                @endif
            @endif
        </section>
    </div>
</main>

<script>
(() => {
    const target = document.getElementById('pay-countdown');
    if (!target) return;
    const expires = new Date(target.dataset.expires).getTime();
    const tick = () => {
        const distance = expires - Date.now();
        if (distance <= 0) {
            target.textContent = @json(__('portal.payments.expired'));
            return;
        }
        const hours = Math.floor(distance / 3600000);
        const minutes = Math.floor((distance % 3600000) / 60000);
        target.textContent = `${hours}h ${String(minutes).padStart(2, '0')}m`;
    };
    tick();
    setInterval(tick, 30000);
})();
</script>

@if($paymentRequest->isPayable() && $paymentReady && str_starts_with((string) config('services.moyasar.publishable_key'), 'pk_'))
<script src="https://cdn.jsdelivr.net/npm/moyasar-payment-form@2.2.10/dist/moyasar.umd.min.js"></script>
<script>
Moyasar.init({
    element: '.mysr-form',
    amount: {{ $paymentRequest->total_amount_minor }},
    currency: @json($paymentRequest->currency),
    description: @json($paymentRequest->title),
    publishable_api_key: @json(config('services.moyasar.publishable_key')),
    callback_url: @json(route('payments.callback', $paymentRequest)),
    methods: ['creditcard'],
    supported_networks: ['mada', 'visa', 'mastercard'],
    apply_coupon: false,
    metadata: { payment_request_uuid: @json($paymentRequest->uuid) },
    on_completed: async function (payment) {
        const response = await fetch(@json(route('payments.public.attempts.store', $paymentRequest)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                id: payment.id,
                status: payment.status,
                amount: payment.amount,
                currency: payment.currency,
                created_at: payment.created_at
            })
        });
        if (!response.ok) throw new Error('Payment reference could not be saved.');
    }
});
</script>
@endif
</body>
</html>
