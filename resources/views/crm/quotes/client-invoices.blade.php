@extends('layouts.dashboard')
@section('title', __('portal.quote.invoices_title'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-file-invoice-dollar"></i> {{ __('portal.quote.invoices_title') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.quote.invoices_subtitle') }}</p>
    </div>
    <div class="invoice-pending">
        <div class="invoice-pending__label">{{ __('portal.quote.pending_payment') }}</div>
        <div class="invoice-pending__value">{{ number_format($pending, 2) }} <span>{{ config('scope.currency', 'SAR') }}</span></div>
    </div>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="card">
    <div class="card-content p-0">
        @if($invoices->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-3x mb-3" style="color:var(--gray-400);"></i>
                <h4 style="color:var(--text-color);">{{ __('portal.quote.no_invoices_title') }}</h4>
                <p class="text-muted">{{ __('portal.quote.no_invoices_body') }}</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('portal.quote.invoice') }}</th>
                        <th>{{ __('portal.quote.project') }}</th>
                        <th class="text-end">{{ __('portal.quote.amount') }}</th>
                        <th>{{ __('portal.quote.payment') }}</th>
                        <th>{{ __('portal.quote.date') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($invoices as $inv)
                    <tr>
                        <td><strong>{{ $inv->quote_number ?: '#'.$inv->id }}</strong><br><small class="text-muted">{{ $inv->title }}</small></td>
                        <td>
                            @if($inv->project)
                                <a href="{{ route('projects.client.show', $inv->project) }}" style="color:var(--primary-color);text-decoration:none;">{{ $inv->project->title }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end" style="font-variant-numeric:tabular-nums;">{{ number_format($inv->invoiceTotal(), 2) }} {{ config('scope.currency', 'SAR') }}</td>
                        <td>
                            @if($inv->isPaid())
                                <span class="status-badge success">{{ __('portal.quote.paid') }}</span>
                            @else
                                <span class="status-badge warning">{{ __('portal.quote.pending') }}</span>
                            @endif
                        </td>
                        <td>{{ optional($inv->paid_at ?? $inv->accepted_at ?? $inv->created_at)->format('M d, Y') }}</td>
                        <td class="text-end"><a href="{{ route('quotes.client.show', $inv) }}" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> {{ __('portal.quote.view') }}</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.invoice-pending{ background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25); border-radius:var(--radius-lg); padding:.6rem 1.1rem; text-align:center; }
.invoice-pending__label{ font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; opacity:.85; }
.invoice-pending__value{ font-size:1.4rem; font-weight:800; }
.invoice-pending__value span{ font-size:.8rem; font-weight:600; opacity:.85; }
</style>
@endpush
