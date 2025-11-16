@extends('layouts.internal-dashboard')
@section('title', 'Team Availability')

@section('breadcrumbs')
<li class="breadcrumb-item active">Team Availability</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Team Availability Overview</h3>
        <span class="badge bg-info">Manager View</span>
    </div>
    <div class="card-content">
        @if($slots->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Team Member</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Booked By</th>
                    <th>Actions</th>
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
                                Past
                            @else
                                {{ ucfirst($slot->status) }}
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
                        <form method="POST" action="{{ route('time-slots.destroy', $slot) }}" style="display:inline;" onsubmit="return confirm('Delete this slot?')">
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
            <h4>No Time Slots</h4>
            <p>Team members haven't created any availability slots yet.</p>
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

