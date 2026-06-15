@extends('layouts.internal-dashboard')
@section('title', __('portal.invoices.manage_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.invoices.manage_title') }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-file-invoice-dollar"></i> {{ __('portal.invoices.manage_title') }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.invoices.manage_subtitle') }} · {{ __('portal.invoices.outstanding') }}: <strong>{{ number_format($outstanding, 2) }} {{ config('scope.currency', 'SAR') }}</strong></p>
    </div>
    <a href="{{ route('invoices.create') }}" class="btn btn-light"><i class="fas fa-plus"></i> {{ __('portal.invoices.new_invoice') }}</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="card-title">{{ __('portal.invoices.all_invoices') }}</span>
        <select class="form-select form-select-sm" style="width:auto;" onchange="window.location='{{ route('invoices.index') }}?status='+this.value">
            <option value="">{{ __('portal.invoices.all_status') }}</option>
            @foreach(['unpaid','proof_submitted','paid','cancelled'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ __('portal.invoices.status.'.$s) }}</option>
            @endforeach
        </select>
    </div>
    <div class="card-content p-0">
        @if($invoices->isEmpty())
            <div class="text-center py-5 text-muted">{{ __('portal.invoices.none_internal') }}</div>
        @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr>
                    <th>{{ __('portal.invoices.invoice') }}</th>
                    <th>{{ __('portal.invoices.client') }}</th>
                    <th>{{ __('portal.invoices.project') }}</th>
                    <th class="text-end">{{ __('portal.invoices.amount') }}</th>
                    <th>{{ __('portal.invoices.status_col') }}</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($invoices as $inv)
                    <tr>
                        <td><strong>{{ $inv->invoice_number }}</strong><br><small class="text-muted">{{ $inv->title }}</small></td>
                        <td>{{ $inv->client?->name ?? '—' }}</td>
                        <td>{{ $inv->project?->title ?? '—' }}</td>
                        <td class="text-end">{{ number_format($inv->amount, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $inv->statusColor() }}">{{ $inv->statusLabel() }}</span>
                            @if($inv->isProofSubmitted())<i class="fas fa-receipt text-info" title="{{ __('portal.invoices.receipt_attached') }}"></i>@endif
                        </td>
                        <td class="text-end"><a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
<div class="d-flex justify-content-center mt-3">{{ $invoices->withQueryString()->links('pagination::bootstrap-5') }}</div>
@endsection
