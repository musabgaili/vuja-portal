@extends('layouts.internal-dashboard')
@section('title', __('portal.spend.approvals'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('spend.index') }}">{{ __('portal.spend.my_title') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.spend.approvals') }}</li>
@endsection

@section('content')
<div class="page-hero">
    <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-inbox"></i> {{ __('portal.spend.approvals') }}</h1>
    <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.spend.approvals_sub') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@php
    $card = function ($r) {
        return $r;
    };
@endphp

{{-- Awaiting decision --}}
<div class="card mb-3"><div class="card-header"><span class="card-title"><i class="fas fa-gavel"></i> {{ __('portal.spend.awaiting_decision') }} <span class="badge bg-warning">{{ $pending->count() }}</span></span></div>
<div class="card-content">
    @forelse($pending as $r)
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong>{{ $r->title }}</strong>
                    <span class="badge bg-{{ $r->isPurchase() ? 'primary' : 'secondary' }}">{{ __('portal.spend.type.'.$r->type) }}</span>
                    <span class="badge bg-light text-dark">{{ __('portal.spend.scope.'.$r->scope) }}</span>
                    <div class="text-muted small mt-1">
                        <i class="fas fa-user"></i> {{ $r->requester->name }} ·
                        {{ $r->isProject() ? ($r->project->title ?? '—') : ($r->category ?: __('portal.spend.general')) }} ·
                        {{ $r->created_at->translatedFormat('M j, Y') }}
                    </div>
                    @if($r->description)<div class="small mt-1">{{ $r->description }}</div>@endif
                    <div class="mt-1">
                        <a href="{{ route('spend.show', $r) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i> {{ __('portal.spend.view_details') }}</a>
                        <span class="text-muted small ms-1">{{ trans_choice('portal.spend.items_count', $r->items->count(), ['count' => $r->items->count()]) }}@if($r->links()) · <i class="fas fa-link"></i> {{ count($r->links()) }}@endif</span>
                    </div>
                </div>
                <div class="text-end"><div style="font-size:1.3rem;font-weight:800;color:var(--primary-color);">{{ number_format((float) $r->amount, 2) }} {{ $r->currency }}</div></div>
            </div>
            <div class="d-flex gap-2 mt-2 flex-wrap">
                <form method="POST" action="{{ route('spend.approve', $r) }}" class="d-flex gap-1 flex-grow-1">@csrf
                    <input type="text" name="review_notes" class="form-control form-control-sm" placeholder="{{ __('portal.spend.notes_ph') }}">
                    <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> {{ __('portal.spend.approve') }}</button>
                </form>
                <form method="POST" action="{{ route('spend.reject', $r) }}" onsubmit="return confirm('{{ __('portal.spend.reject_confirm') }}')">@csrf
                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-ban"></i> {{ __('portal.spend.reject') }}</button>
                </form>
            </div>
        </div>
    @empty
        <p class="text-muted mb-0">{{ __('portal.spend.nothing_to_review') }}</p>
    @endforelse
</div></div>

{{-- Awaiting fulfilment (approved) --}}
<div class="card"><div class="card-header"><span class="card-title"><i class="fas fa-truck"></i> {{ __('portal.spend.awaiting_fulfilment') }} <span class="badge bg-info">{{ $awaiting->count() }}</span></span></div>
<div class="card-content">
    @forelse($awaiting as $r)
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <strong>{{ $r->title }}</strong>
                    <span class="badge bg-{{ $r->isPurchase() ? 'primary' : 'secondary' }}">{{ __('portal.spend.type.'.$r->type) }}</span>
                    <div class="text-muted small mt-1"><i class="fas fa-user"></i> {{ $r->requester->name }} · {{ $r->isProject() ? ($r->project->title ?? '—') : ($r->category ?: __('portal.spend.general')) }}</div>
                    <a href="{{ route('spend.show', $r) }}" class="small"><i class="fas fa-eye"></i> {{ __('portal.spend.view_details') }}</a>
                </div>
                <div class="text-end"><div style="font-weight:700;">{{ number_format((float) $r->amount, 2) }} {{ $r->currency }}</div><small class="text-muted">{{ $r->isPurchase() ? __('portal.spend.estimated') : __('portal.spend.paid') }}</small></div>
            </div>
            @if($r->isPurchase())
                <form method="POST" action="{{ route('spend.purchase', $r) }}" enctype="multipart/form-data" class="row g-1 mt-2 align-items-end">@csrf
                    <div class="col-md-4"><label class="form-label small mb-0">{{ __('portal.spend.actual_cost') }} ({{ $r->currency }})</label><input type="number" step="0.01" min="0" name="actual_amount" class="form-control form-control-sm" value="{{ $r->amount }}" required></div>
                    <div class="col-md-5"><label class="form-label small mb-0">{{ __('portal.spend.actual_receipt') }}</label><input type="file" name="actual_receipts[]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" multiple></div>
                    <div class="col-md-3"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-cart-shopping"></i> {{ __('portal.spend.mark_purchased') }}</button></div>
                </form>
            @else
                <form method="POST" action="{{ route('spend.reimburse', $r) }}" class="mt-2" onsubmit="return confirm('{{ __('portal.spend.reimburse_confirm') }}')">@csrf
                    <button class="btn btn-sm btn-success"><i class="fas fa-hand-holding-dollar"></i> {{ __('portal.spend.mark_reimbursed') }}</button>
                    <small class="text-muted ms-2">{{ __('portal.spend.already_in_ledger') }}</small>
                </form>
            @endif
        </div>
    @empty
        <p class="text-muted mb-0">{{ __('portal.spend.nothing_to_fulfil') }}</p>
    @endforelse
</div></div>
@endsection
