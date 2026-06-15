@extends('layouts.internal-dashboard')
@section('title', __('portal.planner.plan_detail'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('weekly-planner.presence') }}">{{ __('portal.planner.team_presence') }}</a></li>
<li class="breadcrumb-item active">{{ $plan->user->name }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.4rem;"><i class="fas fa-calendar-week"></i> {{ $plan->user->name }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.planner.week_of') }} {{ \Illuminate\Support\Carbon::parse($plan->week_start)->format('D, M j, Y') }}</p>
    </div>
    <span class="badge bg-{{ $plan->statusColor() }}" style="font-size:.9rem;">{{ __('portal.planner.status.'.$plan->status) }}</span>
</div>

@if($plan->review_notes)
<div class="alert alert-warning"><strong>{{ __('portal.planner.review_notes') }}:</strong> {{ $plan->review_notes }}</div>
@endif

{{-- Working location per day --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-location-dot"></i> {{ __('portal.planner.working_location') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0">
            <thead><tr>@foreach($days as $day)<th style="text-transform:capitalize;">{{ ucfirst($day) }}</th>@endforeach</tr></thead>
            <tbody><tr>
                @foreach($days as $day)
                    @php $lk = $plan->locations[$day] ?? null; @endphp
                    <td>{{ $lk ? ($locations[$lk] ?? $lk) : '—' }}</td>
                @endforeach
            </tr></tbody>
        </table>
    </div>
</div>

{{-- Hours logged per line --}}
<div class="card">
    <div class="card-header"><span class="card-title"><i class="fas fa-clock"></i> {{ __('portal.planner.logged_hours') }} — <strong>{{ $plan->totalHours() }}h</strong></span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:760px;">
            <thead>
                <tr>
                    <th>{{ __('portal.planner.item') }}</th>
                    @foreach($days as $day)<th style="text-transform:capitalize;">{{ ucfirst($day) }}</th>@endforeach
                    <th class="text-end">{{ __('portal.planner.total') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($plan->lines as $line)
                <tr>
                    <td><strong>{{ $line->label }}</strong><br><small class="text-muted">{{ $line->category() }}</small></td>
                    @foreach($days as $day)
                        @php $h = (int) ($line->hours[$day] ?? 0); @endphp
                        <td>{{ $h > 0 ? $h.'h' : '—' }}</td>
                    @endforeach
                    <td class="text-end"><strong>{{ $line->totalHours() }}h</strong></td>
                </tr>
            @empty
                <tr><td colspan="{{ count($days) + 2 }}" class="text-muted text-center py-3">{{ __('portal.planner.no_data') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
