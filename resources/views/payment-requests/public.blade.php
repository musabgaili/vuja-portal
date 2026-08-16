<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $paymentRequest->localizedTitle() }} · {{ __('portal.payments.brand') }}</title>
    @include('partials.theme-head')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/moyasar-payment-form@2.2.10/dist/moyasar.css">
    <style>
        body.pay-public {
            margin:0;
            min-height:100vh;
            color: var(--text-color, #142b4a);
            background:
                radial-gradient(circle at 12% -10%, rgba(15,150,156,.18), transparent 42%),
                radial-gradient(circle at 90% 0%, rgba(41,77,97,.12), transparent 36%),
                var(--bg-secondary, #f3f7f8);
            font-family: 'Tajawal', 'Almarai', ui-sans-serif, system-ui, sans-serif;
        }
        .pay-public .theme-toggle-btn {
            border: 1px solid color-mix(in srgb, var(--primary-color) 28%, transparent);
            background: var(--bg-primary, #fff);
            color: var(--primary-color);
            width: 36px; height: 36px; border-radius: 10px;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .pay-shell { width:min(100%,1120px); margin-inline:auto; padding:1rem; }
        .pay-brand { display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:.55rem .15rem 1rem; flex-wrap:wrap; }
        .pay-logo { display:flex; align-items:center; gap:.7rem; color: inherit; text-decoration:none; }
        .pay-logo img { height:40px; width:auto; }
        .pay-logo strong { font-size:1.08rem; letter-spacing:-.02em; color: var(--primary-slate, #294D61); }
        .pay-brand-actions { display:flex; align-items:center; gap:.55rem; flex-wrap:wrap; justify-content:flex-end; }
        .pay-brand-actions .locale-switcher .btn {
            border-color: color-mix(in srgb, var(--primary-color) 28%, transparent);
            color: var(--primary-color);
            background: var(--bg-primary, #fff);
        }
        .secure-pill, .brand-download {
            display:inline-flex; align-items:center; gap:.4rem;
            color: var(--primary-dark, #095055);
            background: var(--primary-light, #ddf0f1);
            padding:.45rem .75rem; border-radius:999px;
            font-size:.72rem; font-weight:800; text-decoration:none;
        }
        .pay-layout {
            display:grid; grid-template-columns:1fr; border-radius:22px; overflow:hidden;
            background: var(--bg-primary, #fff);
            border: 1px solid var(--border-color, #d9e2eb);
            box-shadow: var(--shadow-lg, 0 24px 70px rgba(12,112,117,.12));
        }
        .pay-docket {
            position:relative; padding:1.25rem; color:#fff;
            background: var(--grad-header, linear-gradient(135deg, #0F969C 0%, #0C7075 55%, #294D61 100%));
        }
        .pay-docket::after {
            content:""; position:absolute; inset-inline:16px; bottom:-6px; height:12px;
            background:radial-gradient(circle, var(--bg-secondary, #f3f7f8) 5px, transparent 6px) 0 0/18px 12px repeat-x;
        }
        .docket-label { font-size:.67rem; font-weight:850; letter-spacing:.17em; text-transform:uppercase; color: rgba(255,255,255,.82); }
        .quote-chip {
            display:inline-flex; margin:.55rem 0 .2rem; padding:.35rem .65rem; border-radius:999px;
            background: rgba(255,255,255,.16); color:#fff; font-size:.72rem; font-weight:800; letter-spacing:.06em;
        }
        .pay-docket h1 { margin:.65rem 0 .45rem; font-size:clamp(1.45rem,6vw,2.35rem); line-height:1.12; letter-spacing:-.03em; }
        .docket-desc { color: rgba(255,255,255,.88); line-height:1.65; margin-bottom:1.1rem; }
        .quote-download {
            display:inline-flex; align-items:center; justify-content:center; width:100%; gap:.45rem;
            margin:.35rem 0 1rem; padding:.75rem .9rem; border-radius:11px;
            background:#fff; color: var(--primary-slate, #294D61); text-decoration:none; font-size:.8rem; font-weight:850;
        }
        .docket-rule { border:0; border-top:1px dashed rgba(255,255,255,.28); margin:1.1rem 0; }
        .docket-total { display:flex; justify-content:space-between; align-items:end; gap:1rem; }
        .docket-total span { color: rgba(255,255,255,.78); font-size:.75rem; text-transform:uppercase; letter-spacing:.1em; }
        .docket-total strong { display:block; font-size:clamp(1.8rem,8vw,3rem); line-height:1; }
        .docket-meta { display:grid; grid-template-columns:1fr 1fr; gap:.9rem; margin-top:1.4rem; }
        .docket-meta small { display:block; color: rgba(255,255,255,.72); font-size:.66rem; text-transform:uppercase; letter-spacing:.08em; }
        .docket-meta b { display:block; margin-top:.2rem; font-size:.83rem; word-break:break-word; }
        .expiry-rail { margin-top:1.5rem; border-inline-start:3px solid rgba(255,255,255,.55); padding-inline-start:.75rem; }
        .expiry-rail small { color: rgba(255,255,255,.75); }
        #pay-countdown { font-weight:850; font-variant-numeric:tabular-nums; }
        .pay-stage { padding:1.25rem; background: var(--bg-primary, #fff); }
        .stage-kicker { color: var(--primary-color, #0C7075); font-size:.7rem; font-weight:850; letter-spacing:.14em; text-transform:uppercase; }
        .pay-stage h2 { margin:.4rem 0 .4rem; font-size:1.35rem; color: var(--text-color, #142b4a); }
        .stage-copy { color: var(--text-muted, #607086); line-height:1.6; margin:0 0 1.1rem; }
        .notice { padding:.9rem 1rem; border-radius:12px; margin-bottom:1rem; font-size:.88rem; }
        .notice-success { color:#0b6646; background:#e1f6ec; border:1px solid #b7ead3; }
        .notice-warning { color:#7b5103; background:#fff4d8; border:1px solid #f2dc9e; }
        .notice-danger { color:#902e37; background:#fde7e9; border:1px solid #f3bdc2; }
        .billing-form { display:grid; gap:.9rem; }
        .billing-form label { display:block; margin-bottom:.35rem; color: var(--text-color, #95aecc); font-size:.78rem; font-weight:800; }
        .billing-form input, .billing-form textarea {
            width:100%; border:1px solid var(--border-color, #d9e2eb); border-radius:11px; padding:.85rem;
            font:inherit; background: var(--bg-primary, #fff); color: var(--text-color, #142b4a);
            outline:none; transition:border .18s, box-shadow .18s;
        }
        .billing-form input:focus, .billing-form textarea:focus {
            border-color: var(--primary-color, #0C7075);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 12,112,117), .16);
        }
        .billing-form textarea { min-height:110px; resize:vertical; }
        .field-hint { color: var(--text-muted, #607086); font-size:.72rem; margin-top:.3rem; }
        .field-error { color:#b32636; font-size:.75rem; margin-top:.3rem; }
        .primary-action {
            width:100%; border:0; border-radius:11px; padding:.9rem 1rem; color:#fff;
            background: var(--primary-color, #0C7075); font-weight:850; cursor:pointer;
        }
        .primary-action:hover { background: var(--primary-dark, #095055); }
        .card-head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.75rem; }
        .card-head h3 { margin:0; font-size:1rem; }
        .card-networks { color: var(--text-muted, #607086); font-size:.72rem; }
        .secure-note { display:flex; align-items:flex-start; gap:.55rem; color: var(--text-muted, #607086); font-size:.72rem; line-height:1.5; margin-top:.8rem; }
        .mysr-form { min-height:210px; }
        .mysr-form .mysr-form-button { background: var(--primary-color, #0C7075) !important; }
        [data-bs-theme="dark"] .pay-logo strong { color: #fff; }
        [data-bs-theme="dark"] .pay-stage h2,
        [data-bs-theme="dark"] .card-head h3,
        [data-bs-theme="dark"] .billing-form label,
        [data-bs-theme="dark"] #pay-countdown { color: #fff; }
        [data-bs-theme="dark"] .secure-pill,
        [data-bs-theme="dark"] .brand-download {
            color: #fff;
            background: color-mix(in srgb, var(--primary-color) 28%, transparent);
        }
        [data-bs-theme="dark"] .mysr-form,
        [data-bs-theme="dark"] .mysr-form label,
        [data-bs-theme="dark"] .mysr-form .mysr-label,
        [data-bs-theme="dark"] .mysr-form .mysr-form-label,
        [data-bs-theme="dark"] .mysr-form input,
        [data-bs-theme="dark"] .mysr-form .mysr-input,
        [data-bs-theme="dark"] .mysr-form .mysr-text,
        [data-bs-theme="dark"] .mysr-form p,
        [data-bs-theme="dark"] .mysr-form span { color: #fff !important; }
        [data-bs-theme="dark"] .mysr-form input,
        [data-bs-theme="dark"] .mysr-form .mysr-input {
            background: var(--bg-tertiary, #0b3a40) !important;
            border-color: var(--border-color, #15454c) !important;
        }
        @media (min-width:720px) {
            .pay-shell { padding:2rem; }
            .pay-layout { grid-template-columns:minmax(300px,.9fr) minmax(390px,1.1fr); min-height:650px; }
            .pay-docket, .pay-stage { padding:clamp(1.7rem,4vw,3rem); }
            .pay-docket::after {
                inset-block:16px; inset-inline-start:auto; inset-inline-end:-6px; width:12px; height:auto;
                background:radial-gradient(circle, var(--bg-secondary, #f3f7f8) 5px, transparent 6px) 0 0/12px 18px repeat-y;
            }
        }
        @media (prefers-reduced-motion:no-preference) {
            .pay-layout { animation:pay-enter .45s ease-out both; }
            @keyframes pay-enter { from { opacity:0; transform:translateY(10px); } }
        }
    </style>
</head>
<body class="pay-public">
@php
    $logo = 'images/vd-logo-light.png';
    $quote = $quote ?? $paymentRequest->quote();
    $quoteNumber = $paymentRequest->displayedQuoteNumber();
    $quoteDownloadUrl = $quoteDownloadUrl ?? ($paymentRequest->hasQuoteDownload() ? \App\Actions\Payments\SendPaymentRequestAction::quoteDownloadUrl($paymentRequest) : null);
    $localizedTitle = $paymentRequest->localizedTitle();
    $localizedDescription = $paymentRequest->localizedDescription();
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
            @include('partials.locale-switcher')
            <button type="button" class="theme-toggle-btn" onclick="toggleTheme()" aria-label="{{ __('portal.app.toggle_theme') }}">
                <i data-theme-icon class="fas fa-moon" aria-hidden="true"></i>
            </button>
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
            <h1>{{ $localizedTitle }}</h1>
            <p class="docket-desc">{{ $localizedDescription ?: __('portal.payments.public_intro') }}</p>

            @if($quoteDownloadUrl)
                <div class="docket-label" style="margin-bottom:.55rem">{{ __('portal.payments.quote_details') }}</div>
                <a class="quote-download" href="{{ $quoteDownloadUrl }}">{{ __('portal.payments.download_quote') }}</a>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    description: @json($localizedTitle),
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
