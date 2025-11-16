@extends('layouts.internal-dashboard')
@section('title', 'My Time Slots')

@section('breadcrumbs')
<li class="breadcrumb-item active">My Time Slots</li>
@endsection

@section('content')
<style>
.slots-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}
.table-slots {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.table-slots thead {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}
.table-slots th {
    padding: 1rem;
    font-weight: 600;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.table-slots td {
    padding: 1rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.table-slots tbody tr {
    transition: all 0.2s;
}
.table-slots tbody tr:hover {
    background: #f8fafc;
}
.slot-date {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}
.slot-time {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    background: #f1f5f9;
    border-radius: 6px;
    font-weight: 600;
    color: #475569;
}
.empty-slots {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.empty-slots i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}
</style>

<div class="slots-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700;"><i class="fas fa-calendar-alt"></i> My Availability Schedule</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">Manage your time slots for client meetings</p>
        </div>
        <a href="{{ route('time-slots.create') }}" class="btn btn-light btn-lg">
            <i class="fas fa-plus"></i> Add Availability
        </a>
    </div>
</div>

@if($slots->count() > 0)
<div class="table-slots">
    <table class="table mb-0">
        <thead>
            <tr>
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
                    <div class="slot-date">{{ $slot->date->format('M d, Y') }}</div>
                    <small class="text-muted">{{ $slot->date->format('l') }}</small>
                </td>
                <td>
                    <span class="slot-time">
                        <i class="fas fa-clock"></i> {{ $slot->getFormattedTimeRange() }}
                    </span>
                </td>
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
                        <strong style="color: #1e293b;">{{ $slot->meeting->client->name }}</strong>
                        <br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $slot->meeting->client->email }}</small>
                        @if($slot->meeting->client->phone)
                        <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $slot->meeting->client->phone }}</small>
                        @endif
                        <br><small class="text-info"><i class="fas fa-comment"></i> <strong>{{ $slot->meeting->title }}</strong></small>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        @if(!$slot->isBooked() && !$slot->isPast())
                        <button class="btn btn-sm btn-warning" onclick="toggleBlock({{ $slot->id }})" title="{{ $slot->isBlocked() ? 'Unblock' : 'Block' }}">
                            <i class="fas fa-{{ $slot->isBlocked() ? 'unlock' : 'lock' }}"></i>
                        </button>
                        <form method="POST" action="{{ route('time-slots.destroy', $slot) }}" style="display:inline;" onsubmit="return confirm('Delete this slot?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $slots->links('pagination::bootstrap-5') }}
</div>
@else
<div class="empty-slots">
    <i class="fas fa-calendar-alt"></i>
    <h4 style="color: #1e293b; font-weight: 600;">No Time Slots</h4>
    <p class="text-muted">Create your availability schedule so clients can book meetings with you.</p>
    <a href="{{ route('time-slots.create') }}" class="btn btn-primary btn-lg mt-3">
        <i class="fas fa-plus"></i> Add Time Slots
    </a>
</div>
@endif
@endsection

@push('scripts')
<script>
function toggleBlock(id){fetch(`/internal/time-slots/${id}/toggle-block`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}
</script>
@endpush
