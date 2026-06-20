@extends('layouts.internal-dashboard')
@section('title', __('portal.planner.team_presence'))

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-map-marker-alt"></i> {{ __('portal.planner.team_presence') }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ __('portal.planner.presence_subtitle') }} {{ $weekStart->translatedFormat('D, M j, Y') }}</p>
    </div>
    <a href="{{ route('internal.dashboard') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> {{ __('portal.planner.back') }}</a>
</div>

{{-- High-level: everyone's place of work + hours per day --}}
@include('weekly-planner._overview', ['overview' => $overview, 'days' => $days, 'canDrillIn' => $canDrillIn])

{{-- Where everyone is, by location --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-location-dot"></i> {{ __('portal.planner.by_location') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:720px;">
            <thead>
                <tr>
                    <th>{{ __('portal.planner.location') }}</th>
                    @foreach($days as $day)
                        <th style="text-transform:capitalize;">{{ __('portal.planner.day.'.$day) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($locations as $lk => $ll)
                    <tr>
                        <td><strong>{{ \Illuminate\Support\Facades\Lang::has('portal.planner.location.'.$lk) ? __('portal.planner.location.'.$lk) : $ll }}</strong></td>
                        @foreach($days as $day)
                            @php $people = $grid[$day][$lk] ?? []; @endphp
                            <td>
                                @if(count($people))
                                    <span class="ip-badge" title="{{ implode(', ', $people) }}">{{ count($people) }}</span>
                                    <div class="text-muted" style="font-size:.72rem; margin-top:.25rem;">{{ implode(', ', array_map(fn($n)=>explode(' ', $n)[0], $people)) }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Project managers: who is allocated to the projects you manage this week --}}
@if(!empty($managedAllocation))
<div class="card">
    <div class="card-header"><span class="card-title"><i class="fas fa-diagram-project"></i> {{ __('portal.planner.my_projects_week') }}</span></div>
    <div class="card-content">
        @foreach($managedAllocation as $project => $members)
            <div class="mb-3">
                <strong>{{ $project }}</strong>
                <div class="d-flex flex-wrap gap-2 mt-1">
                    @foreach($members as $m)
                        <span class="badge bg-light text-dark border">{{ $m['name'] }}: {{ $m['hours'] }}h</span>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
