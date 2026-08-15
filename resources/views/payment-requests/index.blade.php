@extends('layouts.internal-dashboard')
@section('title', __('portal.payments.title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.payments.title') }}</li>
@endsection

@push('styles')
<style>
    .paydesk { --ink: var(--gray-900, #142b4a); --muted: var(--gray-600, #607086); --line: var(--gray-200, #d9e2eb); --teal: #1B565E; --cyan: #22aebf; color: var(--ink); }
    .paydesk-top { display:flex; justify-content:space-between; gap:1rem; align-items:flex-end; margin-bottom:1rem; flex-wrap:wrap; }
    .paydesk-kicker { font-size:.72rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color: var(--teal); }
    .paydesk-top h2 { margin:.2rem 0 0; font-size:1.35rem; font-weight:800; }
    .paydesk-top p { margin:.25rem 0 0; color: var(--muted); max-width: 36rem; }
    .pay-new { background: var(--teal); color:#fff; border:0; border-radius:12px; padding:.7rem 1rem; font-weight:750; text-decoration:none; display:inline-flex; align-items:center; gap:.45rem; }
    .pay-new:hover { color:#fff; filter:brightness(1.08); }
    .pay-toolbar { display:flex; gap:.65rem; flex-wrap:wrap; align-items:end; margin-bottom:1rem; padding: .85rem; border:1px solid var(--line); border-radius:16px; background: var(--bg-primary, #fff); }
    .pay-toolbar label { display:block; font-size:.7rem; font-weight:750; color: var(--muted); margin-bottom:.3rem; }
    .pay-toolbar .form-control, .pay-toolbar .form-select { min-height:42px; border-radius:10px; }
    .pay-toolbar .grow { flex: 1 1 220px; }
    .pay-toolbar .status { flex: 0 1 180px; }
    .pay-ledger { border:1px solid var(--line); border-radius:16px; overflow:hidden; background: var(--bg-primary, #fff); }
    .pay-ledger-head, .pay-row { display:grid; grid-template-columns: minmax(0,1.5fr) minmax(0,1.2fr) 140px 120px 150px; gap:1rem; align-items:center; padding:.95rem 1.15rem; }
    .pay-ledger-head { font-size:.68rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color: var(--muted); border-bottom:1px solid var(--line); background: color-mix(in srgb, var(--teal) 6%, transparent); }
    .pay-ledger-head a { color: inherit; text-decoration:none; }
    .pay-row { color: inherit; text-decoration:none; border-bottom:1px solid var(--line); }
    .pay-row:last-child { border-bottom:0; }
    .pay-row:hover { background: color-mix(in srgb, var(--cyan) 8%, transparent); }
    .pay-row strong { display:block; font-size:.95rem; }
    .pay-row small { color: var(--muted); }
    .pay-amount { font-variant-numeric: tabular-nums; font-weight:850; font-size:1.08rem; text-align:end; white-space:nowrap; }
    .pay-chip { display:inline-flex; margin-top:.3rem; padding:.15rem .45rem; border-radius:999px; background: color-mix(in srgb, var(--teal) 10%, transparent); color: var(--teal); font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size:.72rem; font-weight:700; }
    .pay-date { color: var(--muted); font-size:.8rem; }
    @media (max-width: 820px) {
        .pay-ledger-head { display:none; }
        .pay-row { grid-template-columns: 1fr auto; gap:.45rem .9rem; }
        .pay-row .pay-client { grid-column: 1; }
        .pay-row .pay-amount { grid-column: 2; grid-row: 1 / span 2; align-self:start; }
        .pay-row .pay-status, .pay-row .pay-date { grid-column: 1; }
    }
</style>
@endpush

@section('content')
@php
    $sortUrl = function (string $column) use ($filters) {
        return route('payment-requests.index', array_filter([
            'q' => $filters['q'] !== '' ? $filters['q'] : null,
            'status' => $filters['status'] !== '' ? $filters['status'] : null,
            'sort' => $column,
            'direction' => $filters['sort'] === $column && $filters['direction'] === 'asc' ? 'desc' : 'asc',
        ], fn ($value) => $value !== null));
    };
    $sortIcon = function (string $column) use ($filters) {
        if ($filters['sort'] !== $column) {
            return 'fa-sort text-muted';
        }

        return $filters['direction'] === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    };
@endphp

<div class="paydesk">
    <div class="paydesk-top">
        <div>
            <div class="paydesk-kicker">48h card desk</div>
            <h2>{{ __('portal.payments.title') }}</h2>
            <p>{{ __('portal.payments.subtitle') }}</p>
        </div>
        <a class="pay-new" href="{{ route('payment-requests.create') }}"><i class="fas fa-plus"></i>{{ __('portal.payments.new') }}</a>
    </div>

    <form method="GET" action="{{ route('payment-requests.index') }}" class="pay-toolbar">
        <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
        <div class="grow">
            <label for="pay-search">{{ __('portal.payments.search') }}</label>
            <input id="pay-search" type="search" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="{{ __('portal.payments.search_placeholder') }}">
        </div>
        <div class="status">
            <label for="pay-status">{{ __('portal.payments.status') }}</label>
            <select id="pay-status" name="status" class="form-select">
                <option value="">{{ __('portal.payments.all_status') }}</option>
                @foreach(\App\Models\PaymentRequest::STATUSES as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('portal.payments.status.'.$status) }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary" style="min-height:42px;border-radius:10px;"><i class="fas fa-filter me-1"></i>{{ __('portal.payments.filter') }}</button>
        <a href="{{ route('payment-requests.index') }}" class="btn btn-outline-secondary" style="min-height:42px;border-radius:10px;">{{ __('portal.payments.clear') }}</a>
    </form>

    @if($requests->isEmpty())
        <div class="pay-ledger">
            @if($filters['q'] !== '' || $filters['status'] !== '')
                <div class="text-center text-muted py-5">{{ __('portal.payments.empty') }}</div>
            @else
                <x-empty-state icon="fa-credit-card" :title="__('portal.payments.empty')" :text="__('portal.payments.empty_hint')">
                    <a href="{{ route('payment-requests.create') }}" class="pay-new mt-3"><i class="fas fa-plus"></i>{{ __('portal.payments.new') }}</a>
                </x-empty-state>
            @endif
        </div>
    @else
        <div class="pay-ledger">
            <div class="pay-ledger-head">
                <span>{{ __('portal.payments.payment_title') }}</span>
                <span>{{ __('portal.payments.recipient') }}</span>
                <span class="text-end">{{ __('portal.payments.total') }}</span>
                <a href="{{ $sortUrl('status') }}">{{ __('portal.payments.status') }} <i class="fas {{ $sortIcon('status') }}"></i></a>
                <a href="{{ $sortUrl('date') }}">{{ __('portal.payments.created_at') }} <i class="fas {{ $sortIcon('date') }}"></i></a>
            </div>
            @foreach($requests as $item)
                <a href="{{ route('payment-requests.show', $item) }}" class="pay-row">
                    <span>
                        <strong>{{ $item->localizedTitle() }}</strong>
                        @if($item->displayedQuoteNumber())
                            <span class="pay-chip">{{ $item->displayedQuoteNumber() }}</span>
                        @endif
                    </span>
                    <span class="pay-client">{{ $item->name }}<br><small>{{ $item->email }}</small></span>
                    <span class="pay-amount">{{ $item->amount() }} <small>{{ $item->currency }}</small></span>
                    <span class="pay-status"><span class="badge bg-{{ $item->statusColor() }}">{{ $item->statusLabel() }}</span></span>
                    <span class="pay-date">{{ $item->created_at->translatedFormat('M j, Y g:i A') }}</span>
                </a>
            @endforeach
        </div>
        <div class="d-flex justify-content-center mt-3">{{ $requests->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
