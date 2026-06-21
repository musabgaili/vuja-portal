@extends('layouts.internal-dashboard')
@section('title', __('portal.approvals.title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-clipboard-check"></i> {{ __('portal.approvals.title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.approvals.subtitle') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

@php $total = $quotes->count() + $plans->count(); @endphp

@if($total === 0)
    <div class="card"><div class="card-content text-center py-5">
        <i class="fas fa-check-circle" style="font-size:3rem; color:var(--success-color);"></i>
        <h4 class="mt-3">{{ __('portal.approvals.all_clear') }}</h4>
        <p class="text-muted">{{ __('portal.approvals.all_clear_body') }}</p>
    </div></div>
@endif

{{-- Quotes awaiting approval --}}
@if($quotes->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-file-invoice"></i> {{ __('portal.approvals.quotes') }} <span class="badge bg-warning">{{ $quotes->count() }}</span></span></div>
    <div class="card-content p-0">
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.approvals.item') }}</th>
                <th>{{ __('portal.approvals.submitted_by') }}</th>
                <th class="text-end">{{ __('portal.approvals.value') }}</th>
                <th>{{ __('portal.approvals.submitted') }}</th>
                <th class="text-end">{{ __('portal.approvals.action') }}</th>
            </tr></thead>
            <tbody>
                @foreach($quotes as $q)
                    <tr>
                        <td><strong>{{ $q->title }}</strong>@if($q->opportunity)<div class="text-muted" style="font-size:.78rem;">{{ $q->opportunity->name }}</div>@endif</td>
                        <td>{{ $q->creator->name ?? '—' }}</td>
                        <td class="text-end">${{ number_format((float) $q->total_client, 0) }}</td>
                        <td><small class="text-muted">{{ $q->updated_at->diffForHumans() }}</small></td>
                        <td class="text-end"><a href="{{ route('quotes.show', $q) }}" class="btn btn-sm btn-primary"><i class="fas fa-gavel"></i> {{ __('portal.approvals.review') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Timesheets awaiting review (managers) --}}
@if($plans->isNotEmpty())
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-calendar-week"></i> {{ __('portal.approvals.timesheets') }} <span class="badge bg-warning">{{ $plans->count() }}</span></span></div>
    <div class="card-content p-0">
        <table class="table mb-0">
            <thead><tr>
                <th>{{ __('portal.approvals.submitted_by') }}</th>
                <th>{{ __('portal.approvals.week') }}</th>
                <th class="text-end">{{ __('portal.approvals.hours') }}</th>
                <th>{{ __('portal.approvals.submitted') }}</th>
                <th class="text-end">{{ __('portal.approvals.action') }}</th>
            </tr></thead>
            <tbody>
                @foreach($plans as $p)
                    <tr>
                        <td><strong>{{ $p->user->name ?? '—' }}</strong>
                            @if($p->isLate())<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> {{ __('portal.planner.late') }}</span>@endif
                        </td>
                        <td>{{ $p->week_start->translatedFormat('M j, Y') }}</td>
                        <td class="text-end">{{ $p->totalHours() }}h</td>
                        <td><small class="text-muted">{{ optional($p->submitted_at)->diffForHumans() }}</small></td>
                        <td class="text-end"><a href="{{ route('weekly-planner.review', ['week' => $p->week_start->toDateString()]) }}" class="btn btn-sm btn-primary"><i class="fas fa-gavel"></i> {{ __('portal.approvals.review') }}</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
