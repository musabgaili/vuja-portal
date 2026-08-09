@extends('layouts.internal-dashboard')
@section('title', __('portal.payments.new'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('payment-requests.index') }}">{{ __('portal.payments.title') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.payments.new') }}</li>
@endsection

@push('styles')
<style>
    .paydesk { --ink: var(--gray-900, #142b4a); --muted: var(--gray-600, #607086); --line: var(--gray-200, #d9e2eb); --teal: #1B565E; --cyan: #22aebf; color: var(--ink); }
    .paydesk-kicker { font-size:.72rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color: var(--teal); }
    .paydesk h2 { margin:.2rem 0 .85rem; font-size:1.35rem; font-weight:800; }
    .pay-sheet { border:1px solid var(--line); border-radius:18px; background: var(--bg-primary, #fff); overflow:hidden; }
    .pay-block { padding:1.15rem 1.2rem; border-bottom:1px solid var(--line); }
    .pay-block:last-of-type { border-bottom:0; }
    .pay-block h3 { margin:0 0 .85rem; font-size:.78rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color: var(--muted); }
    .pay-grid { display:grid; gap:.85rem; grid-template-columns:1fr; }
    @media (min-width:720px) { .pay-grid { grid-template-columns:1fr 1fr; } .pay-grid .span-2 { grid-column:1 / -1; } }
    .pay-grid label { display:block; font-size:.76rem; font-weight:750; margin-bottom:.35rem; }
    .pay-grid .form-control, .pay-grid .form-select { min-height:46px; border-radius:11px; }
    .pay-quote { display:grid; gap:.85rem; grid-template-columns:1fr; }
    @media (min-width:720px) { .pay-quote { grid-template-columns: minmax(0,.9fr) minmax(0,1.1fr); } }
    .pay-upload { display:flex; align-items:center; justify-content:space-between; gap:.75rem; min-height:46px; padding:.55rem .7rem; border:1px dashed var(--line); border-radius:11px; background: color-mix(in srgb, var(--teal) 4%, transparent); cursor:pointer; }
    .pay-upload:hover { border-color: var(--teal); }
    .pay-upload-btn { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .75rem; border-radius:9px; background: var(--teal); color:#fff; font-size:.8rem; font-weight:750; white-space:nowrap; }
    .pay-upload-name { color: var(--muted); font-size:.8rem; overflow:hidden; text-overflow:ellipsis; }
    .pay-total { display:flex; justify-content:space-between; align-items:end; gap:1rem; padding:1.1rem 1.2rem; background: linear-gradient(135deg, #142b4a, var(--teal)); color:#fff; }
    .pay-total small { display:block; opacity:.75; text-transform:uppercase; letter-spacing:.08em; font-size:.68rem; }
    .pay-total strong { font-size:2rem; line-height:1; font-variant-numeric: tabular-nums; }
    .pay-actions { display:flex; justify-content:space-between; gap:.75rem; flex-wrap:wrap; padding:1rem 1.2rem; }
</style>
@endpush

@section('content')
<div class="paydesk">
    <div class="paydesk-kicker">48h card desk</div>
    <h2>{{ __('portal.payments.new') }}</h2>

    <form method="POST" action="{{ route('payment-requests.store') }}" enctype="multipart/form-data" class="pay-sheet">
        @csrf
        <section class="pay-block">
            <h3>{{ __('portal.payments.recipient') }}</h3>
            <div class="pay-grid">
                <div>
                    <label for="pay-name">{{ __('portal.payments.name') }} *</label>
                    <input id="pay-name" name="name" class="form-control" value="{{ old('name') }}" required maxlength="160" autocomplete="name">
                    @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="pay-email">{{ __('portal.payments.email') }} *</label>
                    <input id="pay-email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="span-2">
                    <label for="pay-phone">{{ __('portal.payments.phone') }}</label>
                    <input id="pay-phone" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
                </div>
            </div>
        </section>

        <section class="pay-block">
            <h3>{{ __('portal.payments.payment_title') }}</h3>
            <div class="pay-grid">
                <div class="span-2">
                    <label for="pay-title">{{ __('portal.payments.payment_title') }} *</label>
                    <input id="pay-title" name="title" class="form-control" value="{{ old('title') }}" required maxlength="180">
                    @error('title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="span-2">
                    <label for="pay-description">{{ __('portal.payments.description') }}</label>
                    <textarea id="pay-description" name="description" rows="3" class="form-control" maxlength="3000">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label for="pay-quantity">{{ __('portal.payments.quantity') }} *</label>
                    <input id="pay-quantity" name="quantity" type="number" class="form-control" value="{{ old('quantity', 1) }}" min="1" max="10000" required>
                </div>
                <div>
                    <label for="pay-amount">{{ __('portal.payments.unit_amount') }} (SAR) *</label>
                    <input id="pay-amount" name="amount" type="number" inputmode="decimal" class="form-control" value="{{ old('amount') }}" min="1" max="99999999.99" step="0.01" required>
                    @error('amount')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <section class="pay-block">
            <h3>{{ __('portal.payments.quote_optional') }}</h3>
            <div class="pay-quote">
                <div>
                    <label for="pay-quote-number">{{ __('portal.payments.quote_number') }}</label>
                    <input id="pay-quote-number" name="quote_number" class="form-control" value="{{ old('quote_number') }}" maxlength="60" placeholder="{{ __('portal.payments.quote_number_placeholder') }}">
                    @error('quote_number')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label for="pay-quote-file">{{ __('portal.payments.quote_file') }}</label>
                    <label class="pay-upload" for="pay-quote-file">
                        <input id="pay-quote-file" type="file" name="quote_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" hidden>
                        <span class="pay-upload-btn"><i class="fas fa-paperclip"></i> {{ __('portal.payments.quote_file_button') }}</span>
                        <span id="pay-quote-file-name" class="pay-upload-name">{{ __('portal.payments.quote_file_none') }}</span>
                    </label>
                    <div class="small text-muted mt-1">{{ __('portal.payments.quote_file_hint') }}</div>
                    @error('quote_file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <div class="pay-total">
            <div><small>{{ __('portal.payments.total') }}</small>{{ __('portal.payments.quantity') }} × {{ __('portal.payments.unit_amount') }}</div>
            <strong><span id="pay-total-value">0.00</span> SAR</strong>
        </div>
        <div class="pay-actions">
            <a href="{{ route('payment-requests.index') }}" class="btn btn-outline-secondary">{{ __('portal.payments.cancel') }}</a>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary" type="submit" name="send" value="0">{{ __('portal.payments.save') }}</button>
                <button class="btn btn-primary" type="submit" name="send" value="1"><i class="fas fa-paper-plane me-1"></i>{{ __('portal.payments.save_send') }}</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const qty = document.getElementById('pay-quantity');
    const amount = document.getElementById('pay-amount');
    const output = document.getElementById('pay-total-value');
    const file = document.getElementById('pay-quote-file');
    const fileName = document.getElementById('pay-quote-file-name');
    const calculate = () => output.textContent = ((Number(qty.value) || 0) * (Number(amount.value) || 0)).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
    qty?.addEventListener('input', calculate);
    amount?.addEventListener('input', calculate);
    file?.addEventListener('change', () => {
        fileName.textContent = file.files?.[0]?.name || @json(__('portal.payments.quote_file_none'));
    });
    calculate();
})();
</script>
@endpush
