@extends('layouts.internal-dashboard')
@section('title', __('portal.payments.title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.payments.title') }}</li>
@endsection

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

<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0;font-size:1.5rem;"><i class="fas fa-credit-card"></i> {{ __('portal.payments.title') }}</h1>
        <p style="margin:.25rem 0 0;opacity:.9;">{{ __('portal.payments.subtitle') }}</p>
    </div>
    <a href="{{ route('payment-requests.create') }}" class="btn btn-light"><i class="fas fa-plus"></i> {{ __('portal.payments.new') }}</a>
</div>

<div class="card mb-3">
    <div class="card-content">
        <form method="GET" action="{{ route('payment-requests.index') }}" class="row g-2 align-items-end">
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="direction" value="{{ $filters['direction'] }}">
            <div class="col-12 col-md-5">
                <label for="pay-search" class="form-label small mb-0">{{ __('portal.payments.search') }}</label>
                <input id="pay-search" type="search" name="q" value="{{ $filters['q'] }}" class="form-control form-control-sm" placeholder="{{ __('portal.payments.search_placeholder') }}">
            </div>
            <div class="col-6 col-md-3">
                <label for="pay-status" class="form-label small mb-0">{{ __('portal.payments.status') }}</label>
                <select id="pay-status" name="status" class="form-select form-select-sm">
                    <option value="">{{ __('portal.payments.all_status') }}</option>
                    @foreach(\App\Models\PaymentRequest::STATUSES as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ __('portal.payments.status.'.$status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4 d-flex gap-2">
                <button class="btn btn-sm btn-primary"><i class="fas fa-filter"></i> {{ __('portal.payments.filter') }}</button>
                <a href="{{ route('payment-requests.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('portal.payments.clear') }}</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="card-title">{{ __('portal.payments.all') }}</span>
        <span class="badge text-bg-light">{{ $requests->total() }}</span>
    </div>
    <div class="card-content p-0">
        @if($requests->isEmpty())
            @if($filters['q'] !== '' || $filters['status'] !== '')
                <div class="text-center py-5 text-muted">{{ __('portal.payments.empty') }}</div>
            @else
                <x-empty-state icon="fa-credit-card" :title="__('portal.payments.empty')" :text="__('portal.payments.empty_hint')">
                    <a href="{{ route('payment-requests.create') }}" class="btn btn-primary mt-3"><i class="fas fa-plus"></i> {{ __('portal.payments.new') }}</a>
                </x-empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('portal.payments.payment_title') }}</th>
                            <th>{{ __('portal.payments.recipient') }}</th>
                            <th class="text-end">{{ __('portal.payments.total') }}</th>
                            <th>
                                <a href="{{ $sortUrl('status') }}" class="text-decoration-none text-reset">
                                    {{ __('portal.payments.status') }} <i class="fas {{ $sortIcon('status') }}"></i>
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortUrl('date') }}" class="text-decoration-none text-reset">
                                    {{ __('portal.payments.created_at') }} <i class="fas {{ $sortIcon('date') }}"></i>
                                </a>
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->title }}</strong>
                                    @if($item->quote()?->quote_number)
                                        <br><small class="text-muted">{{ $item->quote()->quote_number }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $item->name }}
                                    <br><small class="text-muted">{{ $item->email }}</small>
                                </td>
                                <td class="text-end">{{ $item->amount() }} <small class="text-muted">{{ $item->currency }}</small></td>
                                <td><span class="badge bg-{{ $item->statusColor() }}">{{ $item->statusLabel() }}</span></td>
                                <td class="text-nowrap"><small>{{ $item->created_at->translatedFormat('M j, Y g:i A') }}</small></td>
                                <td class="text-end">
                                    <a href="{{ route('payment-requests.show', $item) }}" class="btn btn-sm btn-primary" title="{{ __('portal.payments.view') }}" aria-label="{{ __('portal.payments.view') }}">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
<div class="d-flex justify-content-center mt-3">{{ $requests->links('pagination::bootstrap-5') }}</div>
@endsection
