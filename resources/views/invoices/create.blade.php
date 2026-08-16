@extends('layouts.internal-dashboard')
@section('title', __('portal.invoices.new_invoice'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">{{ __('portal.invoices.manage_title') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.invoices.new_invoice') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-file-circle-plus"></i> {{ __('portal.invoices.new_invoice') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.invoices.new_invoice_sub') }}</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('invoices.store') }}" enctype="multipart/form-data" class="card" novalidate>
    @csrf
    @if($quote)<input type="hidden" name="quote_id" value="{{ $quote->id }}">@endif
    <div class="card-content">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="inv_client_id" class="form-label fw-bold">{{ __('portal.invoices.client') }}</label>
                <select id="inv_client_id" name="client_id" class="form-select">
                    <option value="">{{ __('portal.invoices.guest_no_account') }}</option>
                    @foreach($clients as $c)
                        <option
                            value="{{ $c->id }}"
                            @selected(old('client_id', $quote?->client_id)==$c->id)
                            data-name="{{ $c->name }}"
                            data-email="{{ $c->email }}"
                        >{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
                <small class="text-muted">{{ __('portal.invoices.guest_client_hint') }}</small>
            </div>
            <div class="col-md-6">
                <label for="inv_project_id" class="form-label fw-bold">{{ __('portal.invoices.project') }}</label>
                <select id="inv_project_id" name="project_id" class="form-select">
                    <option value="">{{ __('portal.invoices.no_project') }}</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(old('project_id', $quote?->project_id)==$p->id)>{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="inv_recipient_name" class="form-label fw-bold">{{ __('portal.invoices.recipient_name') }} *</label>
                <input id="inv_recipient_name" type="text" name="recipient_name" class="form-control" value="{{ old('recipient_name', $quote?->client?->name) }}" required maxlength="160" autocomplete="name">
            </div>
            <div class="col-md-6">
                <label for="inv_recipient_email" class="form-label fw-bold">{{ __('portal.invoices.recipient_email') }} *</label>
                <input id="inv_recipient_email" type="text" inputmode="email" name="recipient_email" class="form-control" value="{{ old('recipient_email', $quote?->client?->email) }}" required maxlength="255" autocomplete="email">
                <small class="text-muted">{{ __('portal.invoices.recipient_email_hint') }}</small>
            </div>
            <div class="col-md-8">
                <label for="inv_title" class="form-label fw-bold">{{ __('portal.invoices.invoice_title') }} *</label>
                <input id="inv_title" type="text" name="title" class="form-control" value="{{ old('title', $quote?->title) }}" required placeholder="{{ __('portal.invoices.invoice_title_ph') }}">
            </div>
            <div class="col-md-4">
                <label for="inv_amount" class="form-label fw-bold">{{ __('portal.invoices.amount') }} ({{ config('scope.currency','SAR') }}) *</label>
                <input id="inv_amount" type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount', $quote ? $quote->invoiceTotal() : '') }}" required>
            </div>
            <div class="col-md-4">
                <label for="inv_due_date" class="form-label fw-bold">{{ __('portal.invoices.due_date') }}</label>
                <input id="inv_due_date" type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
            </div>
            <div class="col-md-8">
                <label for="inv_invoice_file" class="form-label fw-bold">{{ __('portal.invoices.invoice_file') }}</label>
                <input id="inv_invoice_file" type="file" name="invoice_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small class="text-muted">{{ __('portal.invoices.invoice_file_hint') }}</small>
            </div>
            <div class="col-12">
                <label for="inv_description" class="form-label fw-bold">{{ __('portal.invoices.description') }}</label>
                <textarea id="inv_description" name="description" rows="3" class="form-control" placeholder="{{ __('portal.invoices.description_ph') }}">{{ old('description') }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.invoices.link_payments') }}</label>
                <p class="small text-muted mb-2">{{ __('portal.invoices.link_payments_hint') }}</p>
                <div id="inv-payments-empty" class="text-muted small py-2">{{ __('portal.invoices.link_payments_pick_email') }}</div>
                <div id="inv-payments-none" class="text-muted small py-2 d-none">{{ __('portal.invoices.link_payments_none') }}</div>
                <div id="inv-payments-list" class="d-none border rounded p-2" style="max-height:260px;overflow:auto;"></div>
                @error('payment_request_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">{{ __('portal.invoices.cancel') }}</a>
        <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.invoices.raise_invoice') }}</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const clientSelect = document.getElementById('inv_client_id');
    const nameInput = document.getElementById('inv_recipient_name');
    const emailInput = document.getElementById('inv_recipient_email');
    const amountInput = document.getElementById('inv_amount');
    const list = document.getElementById('inv-payments-list');
    const empty = document.getElementById('inv-payments-empty');
    const none = document.getElementById('inv-payments-none');
    const payments = @json($paymentRequestsJson);
    const oldSelected = @json(array_map('strval', (array) old('payment_request_ids', [])));

    const normalizeEmail = (value) => String(value || '').trim().toLowerCase();

    const renderPayments = () => {
        const email = normalizeEmail(emailInput.value);
        list.innerHTML = '';
        empty.classList.toggle('d-none', Boolean(email));
        none.classList.add('d-none');
        list.classList.add('d-none');

        if (!email) return;

        const matches = payments.filter((p) => p.email === email);
        if (!matches.length) {
            none.classList.remove('d-none');
            return;
        }

        list.classList.remove('d-none');
        matches.forEach((p) => {
            const checked = oldSelected.includes(String(p.id)) ? 'checked' : '';
            const row = document.createElement('label');
            row.className = 'd-flex align-items-start gap-2 py-2 border-bottom mb-0';
            row.style.cursor = 'pointer';
            row.innerHTML = `
                <input type="checkbox" class="form-check-input mt-1 inv-payment-check" name="payment_request_ids[]" value="${p.id}" data-amount="${p.amount}" ${checked}>
                <span class="flex-grow-1">
                    <strong>${p.title}</strong>
                    <span class="d-block small text-muted">${p.amount} ${p.currency} · ${p.status}${p.paid_at ? ' · ' + p.paid_at : ''}</span>
                </span>
            `;
            list.appendChild(row);
        });
    };

    clientSelect?.addEventListener('change', () => {
        const option = clientSelect.options[clientSelect.selectedIndex];
        if (!option?.value) return;
        if (option.dataset.name) nameInput.value = option.dataset.name;
        if (option.dataset.email) emailInput.value = option.dataset.email;
        renderPayments();
    });

    emailInput?.addEventListener('input', renderPayments);
    emailInput?.addEventListener('change', renderPayments);

    list?.addEventListener('change', (e) => {
        const target = e.target;
        if (!(target instanceof HTMLInputElement) || !target.classList.contains('inv-payment-check')) return;
        if (target.checked && target.dataset.amount && (!amountInput.value || Number(amountInput.value) === 0)) {
            amountInput.value = target.dataset.amount;
        }
    });

    renderPayments();
})();
</script>
@endpush
