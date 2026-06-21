{{-- High-level employee × day matrix: working location + hours per day.
     Expects: $overview, $days, $canDrillIn (bool). --}}
<div class="card mb-3">
    <div class="card-header"><span class="card-title"><i class="fas fa-table-cells"></i> {{ __('portal.planner.week_at_glance') }}</span></div>
    <div class="card-content p-0" style="overflow-x:auto;">
        <table class="table mb-0" style="min-width:760px;">
            <thead>
                <tr>
                    <th>{{ __('portal.planner.employee') }}</th>
                    @foreach($days as $day)<th style="text-transform:capitalize;">{{ __('portal.planner.day.'.$day) }}</th>@endforeach
                    <th class="text-end">{{ __('portal.planner.total') }}</th>
                </tr>
            </thead>
            <tbody>
            @forelse($overview as $row)
                <tr>
                    <td>
                        @if($canDrillIn)
                            <a href="{{ route('weekly-planner.show', $row['plan']) }}" style="color:var(--primary-color);text-decoration:none;"><strong>{{ $row['name'] }}</strong></a>
                        @else
                            <strong>{{ $row['name'] }}</strong>
                        @endif
                        <br><span class="badge bg-{{ $row['plan']->statusColor() }}">{{ __('portal.planner.status.'.$row['status']) }}</span>
                        @if($row['late'] ?? false)<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> {{ __('portal.planner.late') }}</span>@endif
                    </td>
                    @foreach($days as $day)
                        @php $cell = $row['days'][$day]; @endphp
                        <td>
                            @if($cell['leave'] ?? null)
                                <span class="badge bg-warning text-dark" style="white-space:nowrap;"><i class="fas fa-bed"></i> {{ $cell['leave'] }}</span>
                                @if($cell['hours'] > 0)<small class="text-muted d-block">{{ $cell['hours'] }}h</small>@endif
                            @elseif($cell['loc'] || ($cell['window'] ?? null) || $cell['hours'] > 0)
                                @if($cell['loc'])<div style="font-weight:600;">{{ $cell['loc'] }}</div>@endif
                                @if($cell['window'] ?? null)<small class="text-info d-block" style="white-space:nowrap;"><i class="fas fa-clock"></i> {{ $cell['window'] }}</small>@endif
                                @if($cell['hours'] > 0)<small class="text-muted">{{ $cell['hours'] }}h</small>@endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-end"><strong>{{ $row['total'] }}h</strong></td>
                </tr>
            @empty
                <tr><td colspan="{{ count($days) + 2 }}" class="text-muted text-center py-3">{{ __('portal.planner.none_submitted') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
