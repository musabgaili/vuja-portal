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

@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

<form method="POST" action="{{ route('invoices.store') }}" enctype="multipart/form-data" class="card">
    @csrf
    @if($quote)<input type="hidden" name="quote_id" value="{{ $quote->id }}">@endif
    <div class="card-content">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('portal.invoices.client') }} *</label>
                <select name="client_id" class="form-select" required>
                    <option value="">{{ __('portal.invoices.choose_client') }}</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $quote?->client_id)==$c->id)>{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">{{ __('portal.invoices.project') }}</label>
                <select name="project_id" class="form-select">
                    <option value="">{{ __('portal.invoices.no_project') }}</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" @selected(old('project_id', $quote?->project_id)==$p->id)>{{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">{{ __('portal.invoices.invoice_title') }} *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $quote?->title) }}" required placeholder="{{ __('portal.invoices.invoice_title_ph') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.invoices.amount') }} ({{ config('scope.currency','SAR') }}) *</label>
                <input type="number" step="0.01" min="0" name="amount" class="form-control" value="{{ old('amount', $quote ? $quote->invoiceTotal() : '') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">{{ __('portal.invoices.due_date') }}</label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
            </div>
            <div class="col-md-8">
                <label class="form-label fw-bold">{{ __('portal.invoices.invoice_file') }}</label>
                <input type="file" name="invoice_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <small class="text-muted">{{ __('portal.invoices.invoice_file_hint') }}</small>
            </div>
            <div class="col-12">
                <label class="form-label fw-bold">{{ __('portal.invoices.description') }}</label>
                <textarea name="description" rows="3" class="form-control" placeholder="{{ __('portal.invoices.description_ph') }}">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('invoices.index') }}" class="btn btn-secondary">{{ __('portal.invoices.cancel') }}</a>
        <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> {{ __('portal.invoices.raise_invoice') }}</button>
    </div>
</form>
@endsection
