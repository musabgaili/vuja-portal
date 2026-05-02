@extends('layouts.internal-dashboard')
@section('title', __('portal.time_slots.team_slots.page_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.time_slots.team_slots.page_title') }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ __('portal.time_slots.team_slots.title') }}</h3>
        <span class="badge bg-info">{{ __('portal.time_slots.team_slots.manager_badge') }}</span>
    </div>
    <div class="card-content">
        @if($slots->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('portal.time_slots.team_slots.col_member') }}</th>
                    <th>{{ __('portal.time_slots.team_slots.col_date') }}</th>
                    <th>{{ __('portal.time_slots.team_slots.col_time') }}</th>
                    <th>{{ __('portal.time_slots.team_slots.col_status') }}</th>
                    <th>{{ __('portal.time_slots.team_slots.booked_by') }}</th>
                    <th>{{ __('portal.time_slots.team_slots.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slots as $slot)
                <tr>
                    <td>
                        <strong>{{ $slot->user->name }}</strong>
                        <br><small class="text-muted">{{ $slot->user->email }}</small>
                    </td>
                    <td><strong>{{ $slot->date->format('M d, Y') }}</strong><br><small class="text-muted">{{ $slot->date->format('l') }}</small></td>
                    <td>{{ $slot->getFormattedTimeRange() }}</td>
                    <td>
                        <span class="status-badge {{ $slot->getStatusBadgeColor() }}">
                            @if($slot->isPast())
                                {{ __('portal.time_slots.my_slots.past') }}
                            @else
                                {{ __('portal.time_slots.status.'.$slot->status) }}
                            @endif
                        </span>
                    </td>
                    <td>
                        @if($slot->meeting)
                            <strong>{{ $slot->meeting->client->name }}</strong>
                            <br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $slot->meeting->client->email }}</small>
                            @if($slot->meeting->client->phone)
                            <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $slot->meeting->client->phone }}</small>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if(!$slot->isBooked() && !$slot->isPast())
                        <button class="btn btn-sm btn-warning" onclick="toggleBlock({{ $slot->id }})">
                            <i class="fas fa-{{ $slot->isBlocked() ? 'unlock' : 'lock' }}"></i>
                        </button>
                        <form method="POST" action="{{ route('time-slots.destroy', $slot) }}" style="display:inline;" onsubmit="return confirm(@json(__('portal.time_slots.team_slots.delete_confirm')))">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $slots->links('pagination::bootstrap-5') }}
        @else
        <div class="text-center py-5">
            <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
            <h4>{{ __('portal.time_slots.team_slots.empty_title') }}</h4>
            <p>{{ __('portal.time_slots.team_slots.empty_body') }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
@push('scripts')
<script>
function toggleBlock(id){fetch(`/internal/time-slots/${id}/toggle-block`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}
</script>
@endpush
