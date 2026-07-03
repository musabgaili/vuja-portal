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
        @else
        <a href="{{ route('meetings.internal.book') }}" class="btn btn-primary btn-sm">
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
                    <th>{{ __('portal.meetings.with') }}</th>
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
                        <a href="#" onclick="showMeetingDetails('{{ $meeting->getRouteKey() }}');return false;" class="text-decoration-none fw-bold" title="{{ __('portal.meetings.details') }}">
                            {{ $meeting->title }} <i class="fas fa-circle-info text-muted" style="font-size:.8rem;"></i>
                        </a>
                        @if($meeting->description)
                        <br><small class="text-muted">{{ Str::limit($meeting->description, 50) }}</small>
                        @endif
                        @if(!auth()->user()->isClient() && $meeting->attendees->count())
                        <div class="mt-1 d-flex flex-wrap gap-1">
                            @foreach($meeting->attendees as $att)
                            <span class="badge bg-{{ $att->isAccepted() ? 'success' : ($att->isDeclined() ? 'secondary' : 'warning text-dark') }}" style="font-weight:500;">
                                {{ $att->user?->name ?? '—' }} · {{ __('portal.meetings.attendee_status.'.$att->status) }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </td>
                    <td>
                        @php
                            $meUser = auth()->user();
                            $other = $meUser->isClient()
                                ? $meeting->teamMember
                                : ($meeting->team_member_id === $meUser->id ? $meeting->client : $meeting->teamMember);
                        @endphp
                        <strong>{{ $other?->name ?? '—' }}</strong>
                        @if($other?->email)<br><small class="text-muted">{{ $other->email }}</small>@endif
                        @if($other?->phone)<br><small class="text-muted"><i class="fas fa-phone"></i> {{ $other->phone }}</small>@endif
                    </td>
                    <td>
                        {{ $meeting->scheduled_at->translatedFormat('M d, Y') }}
                        <br><small class="text-muted">{{ $meeting->scheduled_at->translatedFormat('g:i A') }}</small>
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
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="showMeetingDetails('{{ $meeting->getRouteKey() }}')">
                            <i class="fas fa-eye"></i> {{ __('portal.meetings.details') }}
                        </button>
                        @if($meeting->team_member_id === auth()->id() && $meeting->isScheduled())
                        <button class="btn btn-sm btn-success" onclick="confirmMeeting(@js($meeting->getRouteKey()))">
                            <i class="fas fa-check"></i> {{ __('portal.meetings.confirm') }}
                        </button>
                        @endif
                        @php $canComplete = ! auth()->user()->isClient() && ! $meeting->isCompleted() && ! $meeting->isCancelled() && ((int) $meeting->team_member_id === auth()->id() || (int) $meeting->client_id === auth()->id() || auth()->user()->isManager() || auth()->user()->isProjectManager()); @endphp
                        @if($canComplete)
                        <form method="POST" action="{{ route('meetings.complete', $meeting) }}" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-check-double"></i> {{ __('portal.meetings.mark_attended') }}</button>
                        </form>
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

        {{-- Hidden per-meeting detail blocks; cloned into the details modal on click. --}}
        @foreach($meetings as $meeting)
            @php
                $meUser = auth()->user();
                $other = $meUser->isClient()
                    ? $meeting->teamMember
                    : ($meeting->team_member_id === $meUser->id ? $meeting->client : $meeting->teamMember);
            @endphp
            <div class="d-none meeting-detail-src" id="mdet-{{ $meeting->getRouteKey() }}">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                    <h5 class="mb-0">{{ $meeting->title }}</h5>
                    <span class="status-badge {{ $meeting->getStatusBadgeColor() }}">{{ $meeting->getStatusLabel() }}</span>
                </div>
                <dl class="row mb-0" style="font-size:.9rem;">
                    <dt class="col-sm-4">{{ __('portal.meetings.with') }}</dt>
                    <dd class="col-sm-8">{{ $other?->name ?? '—' }}@if($other?->email)<br><span class="text-muted">{{ $other->email }}</span>@endif@if($other?->phone)<br><span class="text-muted"><i class="fas fa-phone"></i> {{ $other->phone }}</span>@endif</dd>

                    <dt class="col-sm-4">{{ __('portal.meetings.date_time') }}</dt>
                    <dd class="col-sm-8">{{ $meeting->scheduled_at->translatedFormat('l, M d, Y') }}<br>{{ $meeting->scheduled_at->translatedFormat('g:i A') }} – {{ $meeting->getEndTime()->translatedFormat('g:i A') }} <span class="text-muted">({{ $meeting->duration_minutes }} {{ __('portal.meetings.min') }})</span></dd>

                    @if($meeting->description)
                        <dt class="col-sm-4">{{ __('portal.meetings.description') }}</dt>
                        <dd class="col-sm-8" style="white-space:pre-line;">{{ $meeting->description }}</dd>
                    @endif

                    @if($meeting->meeting_notes)
                        <dt class="col-sm-4">{{ __('portal.meetings.notes') }}</dt>
                        <dd class="col-sm-8" style="white-space:pre-line;">{{ $meeting->meeting_notes }}</dd>
                    @endif

                    @if(!auth()->user()->isClient() && $meeting->attendees->count())
                        <dt class="col-sm-4">{{ __('portal.meetings.attendees') }}</dt>
                        <dd class="col-sm-8 d-flex flex-wrap gap-1">
                            @foreach($meeting->attendees as $att)
                                <span class="badge bg-{{ $att->isAccepted() ? 'success' : ($att->isDeclined() ? 'secondary' : 'warning text-dark') }}" style="font-weight:500;">{{ $att->user?->name ?? '—' }} · {{ __('portal.meetings.attendee_status.'.$att->status) }}</span>
                            @endforeach
                        </dd>
                    @endif
                </dl>
                @if($meeting->meeting_link)
                    <a href="{{ $meeting->meeting_link }}" target="_blank" class="btn btn-sm btn-primary mt-3"><i class="fas fa-video"></i> {{ __('portal.meetings.join') }}</a>
                @endif
            </div>
        @endforeach
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

{{-- Meeting details modal (both roles) --}}
<div class="modal fade" id="meetingDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-calendar-day"></i> {{ __('portal.meetings.details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('portal.team.cancel') }}"></button>
            </div>
            <div class="modal-body" id="meetingDetailBody"></div>
        </div>
    </div>
</div>

@if(auth()->user()->isInternal())
<div class="modal fade" id="confirmModal"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5>{{ __('portal.meetings.confirm_meeting') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" id="confirmForm">@csrf<div class="modal-body"><div class="form-group"><label>{{ __('portal.meetings.meeting_link_optional') }}</label><input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/..."></div></div><div class="modal-footer"><button type="submit" class="btn btn-success">{{ __('portal.meetings.confirm') }}</button></div></form></div></div></div>
@endif
@endsection

@push('scripts')
<script>
function showMeetingDetails(id){
    var src = document.getElementById('mdet-' + id);
    if (!src) return;
    document.getElementById('meetingDetailBody').innerHTML = src.innerHTML;   // server-escaped content
    new bootstrap.Modal(document.getElementById('meetingDetailModal')).show();
}
</script>
@endpush
@if(auth()->user()->isInternal())
@push('scripts')
<script>
function confirmMeeting(id){document.getElementById('confirmForm').action=`/meetings/${id}/confirm`;new bootstrap.Modal(document.getElementById('confirmModal')).show();}
</script>
@endpush
@endif

