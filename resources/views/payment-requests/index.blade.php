@extends('layouts.internal-dashboard')

@section('title', __('portal.payments.title'))

@push('styles')
<style>
    .paydesk { --pay-ink:#183153; --pay-blue:#2457d6; --pay-cyan:#29b6c8; --pay-paper:var(--card-bg,#fff); color:var(--text-color,#27364a); }
    .paydesk-head { display:flex; justify-content:space-between; gap:1rem; align-items:end; margin-bottom:1.25rem; }
    .paydesk-kicker { color:var(--pay-blue); font-size:.72rem; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
    .paydesk-head p { margin:.35rem 0 0; color:var(--gray-600); max-width:620px; }
    .paydesk-grid { display:grid; gap:1rem; grid-template-columns:minmax(0,1fr); }
    .pay-card { background:var(--pay-paper); color:#27364a; border:1px solid var(--border-color,#dde3ed); border-radius:18px; box-shadow:0 12px 32px rgba(29,51,84,.07); overflow:hidden; }
    .pay-card-title { padding:1rem 1rem .85rem; border-bottom:1px solid var(--border-color,#e6eaf0); display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
    .pay-card-title h2 { margin:0; font-size:1rem; font-weight:800; color:var(--pay-ink); }
    .pay-form { padding:1rem; }
    .pay-form-grid { display:grid; grid-template-columns:1fr; gap:.85rem; }
    .pay-form label { display:block; font-size:.76rem; font-weight:750; color:#38506f; margin-bottom:.35rem; }
    .pay-form .form-control { min-height:46px; border-radius:10px; border-color:var(--border-color,#dce2eb); }
    .pay-form textarea.form-control { min-height:92px; }
    .pay-money { display:grid; grid-template-columns:90px 1fr; gap:.75rem; }
    .pay-total { margin-top:1rem; padding:1rem; background:linear-gradient(120deg,#17335a,#2457d6); color:#fff; border-radius:14px; display:flex; justify-content:space-between; align-items:end; }
    .pay-total small { opacity:.72; text-transform:uppercase; letter-spacing:.1em; font-size:.64rem; }
    .pay-total strong { font-size:1.55rem; line-height:1; }
    .pay-actions { display:grid; grid-template-columns:1fr; gap:.65rem; margin-top:1rem; }
    .pay-btn { min-height:45px; border-radius:10px; font-weight:750; }
    .pay-list { padding:.35rem; }
    .pay-row { display:grid; grid-template-columns:1fr auto; gap:.75rem; padding:.85rem; margin:.35rem; color:inherit; text-decoration:none; border-radius:12px; border:1px solid transparent; transition:background .18s,border-color .18s,transform .18s; }
    .pay-row:hover,.pay-row.active { background:rgba(36,87,214,.06); border-color:rgba(36,87,214,.16); transform:translateY(-1px); }
    .pay-row strong { display:block; color:var(--pay-ink); font-size:.9rem; }
    .pay-row small { color:var(--gray-600); }
    .pay-amount { text-align:end; white-space:nowrap; font-weight:800; color:var(--pay-ink); }
    .pay-status { display:inline-flex; align-items:center; gap:.35rem; margin-top:.25rem; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
    .pay-status::before { content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .pay-detail { margin-top:1rem; }
    .pay-detail-body { padding:1rem; display:grid; gap:1rem; }
    .pay-docket { position:relative; padding-inline-start:1rem; border-inline-start:4px solid var(--pay-cyan); }
    .pay-docket h3 { font-weight:850; color:var(--pay-ink); margin:0 0 .4rem; font-size:1.25rem; }
    .pay-docket-total { font-size:2rem; font-weight:900; color:var(--pay-blue); line-height:1.1; }
    .pay-link-box { display:flex; gap:.5rem; flex-wrap:wrap; padding:.75rem; background:var(--body-bg,#f5f7fb); border-radius:12px; }
    .pay-link-box input { min-width:0; flex:1 1 220px; border:0; background:transparent; font-size:.78rem; }
    .pay-timeline { list-style:none; padding:0; margin:0; }
    .pay-timeline li { position:relative; padding:0 0 1rem 1.4rem; border-inline-start:1px solid var(--border-color,#dce2eb); margin-inline-start:.35rem; }
    .pay-timeline li::before { content:""; position:absolute; inset-inline-start:-5px; top:.2rem; width:9px; height:9px; border:2px solid var(--pay-paper); border-radius:50%; background:var(--pay-blue); }
    .pay-timeline li:last-child { padding-bottom:0; }
    .pay-timeline strong { display:block; font-size:.82rem; color:var(--pay-ink); }
    .pay-timeline small { color:var(--gray-600); }
    @media (min-width:640px) {
        .pay-form-grid { grid-template-columns:1fr 1fr; }
        .pay-form-grid .span-2 { grid-column:1 / -1; }
        .pay-actions { grid-template-columns:1fr 1fr; }
    }
    @media (min-width:1050px) {
        .paydesk-grid { grid-template-columns:minmax(360px,.82fr) minmax(420px,1.18fr); align-items:start; }
        .pay-card-title,.pay-form,.pay-detail-body { padding:1.25rem; }
        .pay-detail-body { grid-template-columns:minmax(0,.9fr) minmax(300px,1.1fr); }
    }
</style>
@endpush

@section('content')
<div class="paydesk">
    <div class="paydesk-head">
        <div>
            <div class="paydesk-kicker">48-hour payment desk</div>
            <p>{{ __('portal.payments.subtitle') }}</p>
        </div>
    </div>

    <div class="paydesk-grid">
        <section class="pay-card">
            <div class="pay-card-title">
                <h2><i class="fa-solid fa-paper-plane me-2"></i>{{ __('portal.payments.new') }}</h2>
                <span class="badge text-bg-light">SAR</span>
            </div>
            <form action="{{ route('payment-requests.store') }}" method="POST" class="pay-form">
                @csrf
                <div class="pay-form-grid">
                    <div>
                        <label for="pay-name">{{ __('portal.payments.name') }}</label>
                        <input id="pay-name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="160" autocomplete="name">
                    </div>
                    <div>
                        <label for="pay-email">{{ __('portal.payments.email') }}</label>
                        <input id="pay-email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autocomplete="email">
                    </div>
                    <div>
                        <label for="pay-phone">{{ __('portal.payments.phone') }}</label>
                        <input id="pay-phone" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
                    </div>
                    <div>
                        <label for="pay-title">{{ __('portal.payments.payment_title') }}</label>
                        <input id="pay-title" name="title" class="form-control" value="{{ old('title') }}" required maxlength="180">
                    </div>
                    <div class="span-2">
                        <label for="pay-description">{{ __('portal.payments.description') }}</label>
                        <textarea id="pay-description" name="description" class="form-control" maxlength="3000">{{ old('description') }}</textarea>
                    </div>
                    <div class="span-2 pay-money">
                        <div>
                            <label for="pay-quantity">{{ __('portal.payments.quantity') }}</label>
                            <input id="pay-quantity" name="quantity" type="number" class="form-control" value="{{ old('quantity', 1) }}" min="1" max="10000" required>
                        </div>
                        <div>
                            <label for="pay-amount">{{ __('portal.payments.unit_amount') }} (SAR)</label>
                            <input id="pay-amount" name="amount" type="number" inputmode="decimal" class="form-control" value="{{ old('amount') }}" min="1" max="99999999.99" step="0.01" required>
                        </div>
                    </div>
                </div>
                <div class="pay-total">
                    <div><small>{{ __('portal.payments.total') }}</small><div>Quantity × unit amount</div></div>
                    <strong><span id="pay-total-value">0.00</span> SAR</strong>
                </div>
                <div class="pay-actions">
                    <button class="btn btn-outline-primary pay-btn" type="submit" name="send" value="0">{{ __('portal.payments.save') }}</button>
                    <button class="btn btn-primary pay-btn" type="submit" name="send" value="1">{{ __('portal.payments.save_send') }}</button>
                </div>
            </form>
        </section>

        <section class="pay-card">
            <div class="pay-card-title">
                <h2><i class="fa-solid fa-receipt me-2"></i>{{ __('portal.payments.title') }}</h2>
                <span class="badge text-bg-light">{{ $requests->total() }}</span>
            </div>
            <div class="pay-list">
                @forelse($requests as $item)
                    <a href="{{ route('payment-requests.show', $item) }}" class="pay-row {{ $selected?->is($item) ? 'active' : '' }}">
                        <span>
                            <strong>{{ $item->title }}</strong>
                            <small>{{ $item->name }} · {{ $item->created_at->diffForHumans() }}</small>
                            <span class="pay-status text-{{ $item->statusColor() }}">{{ $item->displayStatus() }}</span>
                        </span>
                        <span class="pay-amount">{{ $item->amount() }}<small class="d-block">{{ $item->currency }}</small></span>
                    </a>
                @empty
                    <div class="text-center text-muted p-5">{{ __('portal.payments.empty') }}</div>
                @endforelse
            </div>
            @if($requests->hasPages())<div class="px-3 pb-3">{{ $requests->links() }}</div>@endif
        </section>
    </div>

    @if($selected)
    <section class="pay-card pay-detail" id="payment-detail">
        <div class="pay-card-title">
            <h2>{{ __('portal.payments.recipient') }} · {{ $selected->name }}</h2>
            <span class="badge text-bg-{{ $selected->statusColor() }}">{{ strtoupper($selected->displayStatus()) }}</span>
        </div>
        <div class="pay-detail-body">
            <div class="pay-docket">
                <h3>{{ $selected->title }}</h3>
                <p class="text-muted mb-3">{{ $selected->description ?: '—' }}</p>
                <div class="pay-docket-total">{{ $selected->amount() }} <small class="fs-6">{{ $selected->currency }}</small></div>
                <div class="small text-muted mt-2">{{ $selected->quantity }} × {{ number_format($selected->unit_amount_minor / 100, 2) }} SAR</div>
                <hr>
                <div class="small"><strong>{{ $selected->email }}</strong><br>{{ $selected->phone ?: '—' }}</div>
                <div class="small text-muted mt-3">{{ __('portal.payments.expires') }}: {{ $selected->expires_at->translatedFormat('M j, Y g:i A') }} UTC</div>
                <div class="pay-link-box mt-3">
                    <input id="payment-public-url" value="{{ $publicUrl }}" readonly aria-label="{{ __('portal.payments.copy_link') }}">
                    <button type="button" class="btn btn-sm btn-dark" id="copy-payment-link"><i class="fa-regular fa-copy me-1"></i>{{ __('portal.payments.copy_link') }}</button>
                </div>
                @if($selected->isPayable())
                <form action="{{ route('payment-requests.send', $selected) }}" method="POST" class="mt-3">
                    @csrf
                    <button class="btn btn-outline-primary w-100 pay-btn"><i class="fa-regular fa-envelope me-2"></i>{{ __('portal.payments.send_again') }}</button>
                </form>
                @endif
            </div>
            <div>
                <h3 class="h6 fw-bold mb-3">{{ __('portal.payments.timeline') }}</h3>
                <ol class="pay-timeline">
                    @forelse($selected->events as $event)
                    <li>
                        <strong>{{ str($event->event_type)->replace('_', ' ')->title() }}</strong>
                        <small>{{ $event->received_at->translatedFormat('M j, Y · g:i:s A') }} UTC · {{ $event->source }}{{ $event->outcome ? ' · '.$event->outcome : '' }}</small>
                    </li>
                    @empty
                    <li><strong>Created</strong></li>
                    @endforelse
                </ol>
            </div>
        </div>
    </section>
    @endif
</div>
@endsection

@push('scripts')
<script>
(() => {
    const qty = document.getElementById('pay-quantity');
    const amount = document.getElementById('pay-amount');
    const output = document.getElementById('pay-total-value');
    const calculate = () => output.textContent = ((Number(qty.value) || 0) * (Number(amount.value) || 0)).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    qty?.addEventListener('input', calculate);
    amount?.addEventListener('input', calculate);
    calculate();

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
})();
</script>
@endpush
