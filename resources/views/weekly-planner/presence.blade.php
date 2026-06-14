@extends('layouts.internal-dashboard')
@section('title', 'Team Presence')

@section('content')
<div class="page-hero">
    <h1 style="margin:0; font-size:1.5rem;"><i class="fas fa-map-marker-alt"></i> Team Presence</h1>
    <p style="margin:.25rem 0 0; opacity:.9;">Where everyone is, week of {{ $weekStart->format('D, M j, Y') }}</p>
</div>

<div class="card">
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:720px;">
            <thead>
                <tr>
                    <th>Location</th>
                    @foreach($days as $day)
                        <th style="text-transform:capitalize;">{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($locations as $lk => $ll)
                    <tr>
                        <td><strong>{{ $ll }}</strong></td>
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
@endsection
