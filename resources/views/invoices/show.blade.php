@extends('layouts.internal-dashboard')
@section('title', $invoice->invoice_number)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">{{ __('portal.invoices.manage_title') }}</a></li>
<li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.4rem;"><i class="fas fa-file-invoice-dollar"></i> {{ $invoice->invoice_number }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ $invoice->title }}</p>
    </div>
    <span class="badge bg-{{ $invoice->statusColor() }}" style="font-size:.9rem;">{{ $invoice->statusLabel() }}</span>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">{{ __('portal.invoices.details') }}</span></div>
            <div class="card-content">
                <table class="table mb-0">
                    <tr><td>{{ __('portal.invoices.client') }}</td><td class="text-end">{{ $invoice->client?->name }} <small class="text-muted">{{ $invoice->client?->email }}</small></td></tr>
                    <tr><td>{{ __('portal.invoices.project') }}</td><td class="text-end">@if($invoice->project)<a href="{{ route('projects.manager.show', $invoice->project) }}">{{ $invoice->project->title }}</a>@else — @endif</td></tr>
                    <tr><td>{{ __('portal.invoices.amount') }}</td><td class="text-end"><strong>{{ number_format($invoice->amount, 2) }} {{ $invoice->currency }}</strong></td></tr>
                    @if($invoice->due_date)<tr><td>{{ __('portal.invoices.due_date') }}</td><td class="text-end">{{ $invoice->due_date->format('M d, Y') }}</td></tr>@endif
                    <tr><td>{{ __('portal.invoices.raised_by') }}</td><td class="text-end">{{ $invoice->creator?->name }} · {{ $invoice->created_at->format('M d, Y') }}</td></tr>
                </table>
                @if($invoice->description)<p class="text-muted mt-3 mb-0">{{ $invoice->description }}</p>@endif
                @if($invoice->hasInvoiceFile())
                    <a href="{{ route('invoices.file', $invoice) }}" class="btn btn-sm btn-light mt-3"><i class="fas fa-file-arrow-down"></i> {{ __('portal.invoices.download_invoice') }}</a>
                @endif
            </div>
        </div>

        {{-- Client payment proof --}}
        <div class="card">
            <div class="card-header"><span class="card-title"><i class="fas fa-receipt"></i> {{ __('portal.invoices.payment_proof') }}</span></div>
            <div class="card-content">
                @if($invoice->hasReceipt())
                    <p class="mb-2">{{ __('portal.invoices.receipt_submitted_on') }} <strong>{{ optional($invoice->receipt_uploaded_at)->format('M d, Y g:i A') }}</strong></p>
                    <a href="{{ route('invoices.receipt', $invoice) }}" class="btn btn-sm btn-light"><i class="fas fa-download"></i> {{ __('portal.invoices.view_receipt') }}</a>
                @else
                    <p class="text-muted mb-0">{{ __('portal.invoices.no_receipt_yet') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.invoices.actions') }}</span></div>
            <div class="card-content">
                @if($invoice->isPaid())
                    <div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ __('portal.invoices.paid_on') }} {{ optional($invoice->paid_at)->format('M d, Y') }}</div>
                    <form method="POST" action="{{ route('invoices.reopen', $invoice) }}">@csrf
                        <button class="btn btn-outline-primary w-100"><i class="fas fa-rotate-left"></i> {{ __('portal.invoices.reopen') }}</button>
                    </form>
                @elseif($invoice->isCancelled())
                    <p class="text-muted">{{ __('portal.invoices.cancelled_note') }}</p>
                @else
                    @if($invoice->isProofSubmitted())
                        <div class="alert alert-info"><i class="fas fa-hourglass-half"></i> {{ __('portal.invoices.proof_review_note') }}</div>
                    @else
                        <p class="text-muted">{{ __('portal.invoices.awaiting_receipt_note') }}</p>
                    @endif
                    <form method="POST" action="{{ route('invoices.mark-paid', $invoice) }}" class="mb-2">@csrf
                        <button class="btn btn-success w-100"><i class="fas fa-check"></i> {{ __('portal.invoices.confirm_paid') }}</button>
                    </form>
                    <form method="POST" action="{{ route('invoices.cancel', $invoice) }}" onsubmit="return confirm('{{ __('portal.invoices.cancel_confirm') }}')">@csrf
                        <button class="btn btn-outline-danger w-100"><i class="fas fa-ban"></i> {{ __('portal.invoices.cancel_invoice') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
