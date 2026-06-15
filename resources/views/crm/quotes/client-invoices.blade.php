@extends('layouts.dashboard')
@section('title', __('portal.invoices.title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-file-invoice-dollar"></i> {{ __('portal.invoices.title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.invoices.client_subtitle') }}</p>
    </div>
    <div class="invoice-pending">
        <div class="invoice-pending__label">{{ __('portal.invoices.outstanding') }}</div>
        <div class="invoice-pending__value">{{ number_format($outstanding, 2) }} <span>{{ config('scope.currency', 'SAR') }}</span></div>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

@if($invoices->isEmpty())
    <div class="card"><div class="card-content text-center py-5">
        <i class="fas fa-file-invoice fa-3x mb-3" style="color:var(--gray-400);"></i>
        <h4 style="color:var(--text-color);">{{ __('portal.invoices.none_title') }}</h4>
        <p class="text-muted">{{ __('portal.invoices.none_body') }}</p>
    </div></div>
@else
    @foreach($invoices as $inv)
    <div class="card mb-3" style="border-inline-start:4px solid var(--primary-color);">
        <div class="card-content">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-1">{{ $inv->title }}</h4>
                    <div class="text-muted" style="font-size:.85rem;">
                        <strong>{{ $inv->invoice_number }}</strong>
                        @if($inv->project) · <a href="{{ route('projects.client.show', $inv->project) }}" style="color:var(--primary-color);text-decoration:none;">{{ $inv->project->title }}</a>@endif
                        @if($inv->due_date) · {{ __('portal.invoices.due') }} {{ $inv->due_date->format('M d, Y') }}@endif
                    </div>
                    @if($inv->description)<p class="text-muted mt-1 mb-0" style="font-size:.85rem;">{{ $inv->description }}</p>@endif
                </div>
                <div class="text-end">
                    <div style="font-size:1.4rem; font-weight:800;">{{ number_format($inv->amount, 2) }} <small class="text-muted">{{ $inv->currency }}</small></div>
                    <span class="badge bg-{{ $inv->statusColor() }}">{{ $inv->statusLabel() }}</span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3" style="border-top:1px solid var(--gray-200);">
                <div class="d-flex gap-2 flex-wrap">
                    @if($inv->hasInvoiceFile())
                        <a href="{{ route('invoices.client.file', $inv) }}" class="btn btn-sm btn-light"><i class="fas fa-file-arrow-down"></i> {{ __('portal.invoices.download_invoice') }}</a>
                    @endif
                    @if($inv->hasReceipt())
                        <a href="{{ route('invoices.client.receipt-file', $inv) }}" class="btn btn-sm btn-light"><i class="fas fa-receipt"></i> {{ __('portal.invoices.your_receipt') }}</a>
                    @endif
                </div>

                @if($inv->isPaid())
                    <span class="text-success"><i class="fas fa-circle-check"></i> {{ __('portal.invoices.paid_on') }} {{ optional($inv->paid_at)->format('M d, Y') }}</span>
                @elseif($inv->isProofSubmitted())
                    <span class="text-info"><i class="fas fa-hourglass-half"></i> {{ __('portal.invoices.under_review') }}</span>
                @endif
            </div>

            {{-- Receipt upload (proof of payment) --}}
            @if($inv->awaitingPayment())
            <form method="POST" action="{{ route('invoices.client.receipt', $inv) }}" enctype="multipart/form-data" class="mt-3">
                @csrf
                <label class="form-label fw-bold mb-1">{{ $inv->hasReceipt() ? __('portal.invoices.replace_receipt') : __('portal.invoices.upload_receipt') }}</label>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required style="max-width:360px;">
                    <button class="btn btn-primary"><i class="fas fa-upload"></i> {{ __('portal.invoices.submit_proof') }}</button>
                </div>
                <small class="text-muted">{{ __('portal.invoices.upload_hint') }}</small>
            </form>
            @endif
        </div>
    </div>
    @endforeach
@endif
@endsection

@push('styles')
<style>
.invoice-pending{ background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); border-radius:var(--radius-lg); padding:.6rem 1.1rem; text-align:center; }
.invoice-pending__label{ font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; opacity:.85; }
.invoice-pending__value{ font-size:1.4rem; font-weight:800; }
.invoice-pending__value span{ font-size:.8rem; font-weight:600; opacity:.85; }
</style>
@endpush
