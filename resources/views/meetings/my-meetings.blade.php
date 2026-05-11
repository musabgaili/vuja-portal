@extends(auth()->user()->isClient() ? 'layouts.dashboard' : 'layouts.internal-dashboard')
@section('title', __('portal.meetings.my_meetings'))
@section('page-title', __('portal.meetings.my_meetings'))

@if(!auth()->user()->isClient())
@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.meetings.my_meetings') }}</li>
@endsection
@endif

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ __('portal.meetings.my_meetings') }}</h3>
        @if(auth()->user()->isClient())
        <a href="{{ route('meetings.available-slots') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> {{ __('portal.meetings.book_new_meeting') }}
        </a>
        @endif
    </div>
    <div class="card-content">
        @if($meetings->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('portal.manager_legacy.col_title') }}</th>
                    @if(auth()->user()->isClient())
                    <th>{{ __('portal.meetings.with') }}</th>
                    @else
                    <th>{{ __('portal.manager_legacy.col_client') }}</th>
                    @endif
                    <th>{{ __('portal.meetings.date_time') }}</th>
                    <th>{{ __('portal.meetings.duration') }}</th>
                    <th>{{ __('portal.manager_legacy.col_status') }}</th>
                    <th>{{ __('portal.manager_legacy.col_actions') }}</th>
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
                    <td>{{ $meeting->duration_minutes }} {{ __('portal.meetings.min') }}</td>
                    <td>
                        <span class="status-badge {{ $meeting->getStatusBadgeColor() }}">
                            {{ $meeting->getStatusLabel() }}
                        </span>
                        @if($meeting->meeting_link)
                        <br><a href="{{ $meeting->meeting_link }}" target="_blank" class="btn btn-sm btn-primary mt-1">
                            <i class="fas fa-video"></i> {{ __('portal.meetings.join') }}
                        </a>
                        @endif
                    </td>
                    <td>
                        @if(auth()->user()->isInternal() && $meeting->isScheduled())
                        <button class="btn btn-sm btn-success" onclick="confirmMeeting(@js($meeting->getRouteKey()))">
                            <i class="fas fa-check"></i> {{ __('portal.meetings.confirm') }}
                        </button>
                        @endif
                        @if(!$meeting->isCompleted() && !$meeting->isCancelled())
                        <form method="POST" action="{{ route('meetings.cancel', $meeting) }}" style="display:inline;" onsubmit="return confirm('{{ __('portal.meetings.confirm_cancel') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
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
            <h4>{{ __('portal.meetings.no_meetings') }}</h4>
            @if(auth()->user()->isClient())
            <p>{{ __('portal.meetings.book_first_meeting') }}</p>
            <a href="{{ route('meetings.available-slots') }}" class="btn btn-primary">
                <i class="fas fa-calendar-check"></i> {{ __('portal.meetings.view_available_slots') }}
            </a>
            @else
            <p>{{ __('portal.meetings.no_meetings_scheduled') }}</p>
            @endif
        </div>
        @endif
    </div>
</div>

@if(auth()->user()->isInternal())
<div class="modal fade" id="confirmModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.meetings.confirm_meeting') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" id="confirmForm">@csrf<div class="modal-body"><div class="form-group"><label>{{ __('portal.meetings.meeting_link_optional') }}</label><input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..."></div></div><div class="modal-footer"><button type="submit" class="btn btn-success">{{ __('portal.meetings.confirm') }}</button></div></form></div></div></div>
@endif
@endsection
@if(auth()->user()->isInternal())
@push('scripts')
<script>
function confirmMeeting(id){document.getElementById('confirmForm').action=`/meetings/${id}/confirm`;new bootstrap.Modal(document.getElementById('confirmModal')).show();}
</script>
@endpush
@endif

