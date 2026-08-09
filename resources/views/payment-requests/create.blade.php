@extends('layouts.internal-dashboard')
@section('title', __('portal.payments.new'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('payment-requests.index') }}">{{ __('portal.payments.title') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.payments.new') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-file-circle-plus"></i> {{ __('portal.payments.new') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.payments.subtitle') }}</p>
</div>

<form method="POST" action="{{ route('payment-requests.store') }}" class="card">
    @csrf
    <div class="card-content">
        <div class="row g-3">
            <div class="col-12">
                <label for="pay-quote" class="form-label fw-bold">{{ __('portal.payments.quote_optional') }}</label>
                <select id="pay-quote" name="quote_id" class="form-select">
                    <option value="">{{ __('portal.payments.quote_none') }}</option>
                    @foreach($quotes as $option)
                        <option value="{{ $option->id }}"
                            @selected((string) old('quote_id', $quote?->id) === (string) $option->id)
                            data-name="{{ $option->client_name }}"
                            data-email="{{ $option->client_email }}"
                            data-title="{{ $option->title }}"
                            data-amount="{{ number_format($option->invoiceTotal(), 2, '.', '') }}">
                            {{ $option->quote_number ?: '#'.$option->id }} · {{ $option->title }} · {{ number_format($option->invoiceTotal(), 2) }} SAR
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="pay-name" class="form-label fw-bold">{{ __('portal.payments.name') }} *</label>
                <input id="pay-name" name="name" class="form-control" value="{{ old('name', $quote?->client_name) }}" required maxlength="160" autocomplete="name">
            </div>
            <div class="col-md-6">
                <label for="pay-email" class="form-label fw-bold">{{ __('portal.payments.email') }} *</label>
                <input id="pay-email" name="email" type="email" class="form-control" value="{{ old('email', $quote?->client_email) }}" required autocomplete="email">
            </div>
            <div class="col-md-6">
                <label for="pay-phone" class="form-label fw-bold">{{ __('portal.payments.phone') }}</label>
                <input id="pay-phone" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="40" autocomplete="tel">
            </div>
            <div class="col-md-6">
                <label for="pay-title" class="form-label fw-bold">{{ __('portal.payments.payment_title') }} *</label>
                <input id="pay-title" name="title" class="form-control" value="{{ old('title', $quote?->title) }}" required maxlength="180">
            </div>
            <div class="col-12">
                <label for="pay-description" class="form-label fw-bold">{{ __('portal.payments.description') }}</label>
                <textarea id="pay-description" name="description" rows="3" class="form-control" maxlength="3000">{{ old('description') }}</textarea>
            </div>
            <div class="col-md-4">
                <label for="pay-quantity" class="form-label fw-bold">{{ __('portal.payments.quantity') }} *</label>
                <input id="pay-quantity" name="quantity" type="number" class="form-control" value="{{ old('quantity', 1) }}" min="1" max="10000" required>
            </div>
            <div class="col-md-8">
                <label for="pay-amount" class="form-label fw-bold">{{ __('portal.payments.unit_amount') }} (SAR) *</label>
                <input id="pay-amount" name="amount" type="number" inputmode="decimal" class="form-control" value="{{ old('amount', $quote ? number_format($quote->invoiceTotal(), 2, '.', '') : '') }}" min="1" max="99999999.99" step="0.01" required>
            </div>
            <div class="col-12">
                <div class="alert alert-light border mb-0 d-flex justify-content-between align-items-center">
                    <span>{{ __('portal.payments.total') }} · {{ __('portal.payments.quantity') }} × {{ __('portal.payments.unit_amount') }}</span>
                    <strong><span id="pay-total-value">0.00</span> SAR</strong>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between flex-wrap gap-2">
        <a href="{{ route('payment-requests.index') }}" class="btn btn-secondary">{{ __('portal.payments.cancel') }}</a>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" type="submit" name="send" value="0">{{ __('portal.payments.save') }}</button>
            <button class="btn btn-primary" type="submit" name="send" value="1"><i class="fas fa-paper-plane"></i> {{ __('portal.payments.save_send') }}</button>
        </div>
    </div>
</form>
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
    document.getElementById('pay-quote')?.addEventListener('change', (event) => {
        const option = event.target.selectedOptions[0];
        if (!option?.value) return;
        const name = document.getElementById('pay-name');
        const email = document.getElementById('pay-email');
        const title = document.getElementById('pay-title');
        if (option.dataset.name) name.value = option.dataset.name;
        if (option.dataset.email) email.value = option.dataset.email;
        if (option.dataset.title) title.value = option.dataset.title;
        if (option.dataset.amount) amount.value = option.dataset.amount;
        calculate();
    });
    calculate();
})();
</script>
@endpush
