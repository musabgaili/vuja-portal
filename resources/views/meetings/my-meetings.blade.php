@extends(auth()->user()->isClient() ? 'layouts.dashboard' : 'layouts.internal-dashboard')
@section('title', 'My Meetings')

@if(!auth()->user()->isClient())
@section('breadcrumbs')
<li class="breadcrumb-item active">My Meetings</li>
@endsection
@endif

@section('content')
<div class="card">
    <div class="card-header">
        <h3>My Meetings</h3>
        @if(auth()->user()->isClient())
        <a href="{{ route('meetings.available-slots') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Book New Meeting
        </a>
        @endif
    </div>
    <div class="card-content">
        @if($meetings->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Title</th>
                    @if(auth()->user()->isClient())
                    <th>With</th>
                    @else
                    <th>Client</th>
                    @endif
                    <th>Date & Time</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($meetings as $meeting)
                <tr>
                    <td>
                        <strong>{{ $meeting->title }}</strong>
                        @if($meeting->description)
                        <br><small class="text-muted">{{ Str::limit($meeting->description, 50) }}</small>
                        @endif
                    </td>
                    <td>
                        @if(auth()->user()->isClient())
                        <strong>{{ $meeting->teamMember->name }}</strong>
                        <br><small class="text-muted">{{ $meeting->teamMember->email }}</small>
                        @else
                        <strong>{{ $meeting->client->name }}</strong>
                        <br><small class="text-muted">{{ $meeting->client->email }}</small>
                        @if($meeting->client->phone)
                        <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $meeting->client->phone }}</small>
                        @endif
                        @endif
                    </td>
                    <td>
                        {{ $meeting->scheduled_at->format('M d, Y') }}
                        <br><small class="text-muted">{{ $meeting->scheduled_at->format('g:i A') }}</small>
                    </td>
                    <td>{{ $meeting->duration_minutes }} min</td>
                    <td>
                        <span class="status-badge {{ $meeting->getStatusBadgeColor() }}">
                            {{ ucfirst($meeting->status) }}
                        </span>
                        @if($meeting->meeting_link)
                        <br><a href="{{ $meeting->meeting_link }}" target="_blank" class="btn btn-sm btn-primary mt-1">
                            <i class="fas fa-video"></i> Join
                        </a>
                        @endif
                    </td>
                    <td>
                        @if(auth()->user()->isInternal() && $meeting->isScheduled())
                        <button class="btn btn-sm btn-success" onclick="confirmMeeting({{ $meeting->id }})">
                            <i class="fas fa-check"></i> Confirm
                        </button>
                        @endif
                        @if(!$meeting->isCompleted() && !$meeting->isCancelled())
                        <form method="POST" action="{{ route('meetings.cancel', $meeting) }}" style="display:inline;" onsubmit="return confirm('Cancel this meeting?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $meetings->links('pagination::bootstrap-5') }}
        @else
        <div class="text-center py-5">
            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
            <h4>No Meetings</h4>
            @if(auth()->user()->isClient())
            <p>Book your first meeting with our team.</p>
            <a href="{{ route('meetings.available-slots') }}" class="btn btn-primary">
                <i class="fas fa-calendar-check"></i> View Available Slots
            </a>
            @else
            <p>No meetings scheduled yet.</p>
            @endif
        </div>
        @endif
    </div>
</div>

@if(auth()->user()->isInternal())
<div class="modal fade" id="confirmModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>Confirm Meeting</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" id="confirmForm">@csrf<div class="modal-body"><div class="form-group"><label>Meeting Link (Optional)</label><input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..."></div></div><div class="modal-footer"><button type="submit" class="btn btn-success">Confirm</button></div></form></div></div></div>
@endif
@endsection
@if(auth()->user()->isInternal())
@push('scripts')
<script>
function confirmMeeting(id){document.getElementById('confirmForm').action=`/meetings/${id}/confirm`;new bootstrap.Modal(document.getElementById('confirmModal')).show();}
</script>
@endpush
@endif

