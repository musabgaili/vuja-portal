@extends('layouts.internal-dashboard')
@section('title', __('portal.planner.review_title'))

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-clipboard-check"></i> {{ __('portal.planner.review_title') }}</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.planner.week_of') }} {{ $weekStart->format('D, M j, Y') }}</p>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('weekly-planner.presence') }}" class="btn btn-outline-primary"><i class="fas fa-map-marker-alt"></i> {{ __('portal.planner.team_presence') }}</a>
</div>

@include('weekly-planner._overview', ['overview' => $overview, 'days' => $days, 'canDrillIn' => true])

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.planner.pending_approvals') }} ({{ $pending->count() }})</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('portal.planner.employee') }}</th><th>{{ __('portal.planner.breakdown') }}</th><th>{{ __('portal.planner.total') }}</th><th class="text-end">{{ __('portal.planner.action') }}</th></tr></thead>
                    <tbody>
                    @forelse($pending as $p)
                        <tr>
                            <td>{{ $p->user->name }}</td>
                            <td>
                                @foreach($p->categoryBreakdown() as $cat => $h)
                                    <span class="badge bg-light text-dark border">{{ $cat }}: {{ $h }}h</span>
                                @endforeach
                            </td>
                            <td><strong>{{ $p->totalHours() }}h</strong></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('weekly-planner.approve', $p) }}" class="d-inline">@csrf
                                    <button class="btn btn-primary btn-sm"><i class="fas fa-check"></i></button>
                                </form>
                                <form method="POST" action="{{ route('weekly-planner.reject', $p) }}" class="d-inline">@csrf
                                    <button class="btn btn-outline-primary btn-sm" title="{{ __('portal.planner.send_back') }}"><i class="fas fa-rotate-left"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted text-center py-3">{{ __('portal.planner.none_pending') }} 🎉</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><span class="card-title">{{ __('portal.planner.all_plans') }}</span></div>
            <div class="card-content p-0">
                <table class="table mb-0">
                    <thead><tr><th>{{ __('portal.planner.employee') }}</th><th>{{ __('portal.planner.total') }}</th><th>{{ __('portal.planner.field_status') }}</th></tr></thead>
                    <tbody>
                    @forelse($plans as $p)
                        <tr><td>{{ $p->user->name }}</td><td>{{ $p->totalHours() }}h</td>
                            <td><span class="badge bg-{{ $p->statusColor() }}">{{ __('portal.planner.status.'.$p->status) }}</span></td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center py-3">{{ __('portal.planner.none_submitted') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Category breakdown across all submitted timesheets --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.planner.time_breakdown') }}</span></div>
            <div class="card-content">
                @if($breakdownTotal > 0)
                    <div class="text-center mb-3">
                        <strong style="font-size:1.5rem; color:var(--primary-color);">{{ $breakdownTotal }}h</strong>
                        <div class="text-muted" style="font-size:.8rem;">{{ __('portal.planner.total_logged') }}</div>
                    </div>
                    @foreach($breakdown as $cat => $h)
                        @php $pct = round($h / $breakdownTotal * 100); @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between" style="font-size:.85rem;"><span>{{ $cat }}</span><span>{{ $h }}h ({{ $pct }}%)</span></div>
                            <div style="height:8px; background:var(--bg-tertiary); border-radius:999px; overflow:hidden;">
                                <div style="height:100%; width:{{ $pct }}%; background:var(--grad-header); border-radius:999px;"></div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center py-3">{{ __('portal.planner.no_data') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
