@extends('layouts.internal-dashboard')
@section('title', __('portal.time_slots.team_slots.page_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.time_slots.team_slots.page_title') }}</li>
@endsection

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

{{-- Roster: every internal team member, including those with no availability yet --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">{{ __('portal.time_slots.team_slots.roster_title') }}</h3>
        <span class="badge bg-info">{{ __('portal.time_slots.team_slots.manager_badge') }}</span>
    </div>
    <div class="card-content">
        <div class="row g-3">
            @foreach($teamMembers as $member)
            @php $count = (int) ($availableCounts[$member->id] ?? 0); @endphp
            <div class="col-md-6 col-lg-4">
                <div class="roster-card">
                    <div class="roster-info">
                        <strong>{{ $member->name }}</strong>
                        <small class="text-muted d-block">{{ $member->email }}</small>
                        @if($count > 0)
                            <span class="badge bg-success mt-1">{{ trans_choice('portal.time_slots.team_slots.available_count', $count, ['count' => $count]) }}</span>
                        @else
                            <span class="badge bg-secondary mt-1">{{ __('portal.time_slots.team_slots.no_available') }}</span>
                        @endif
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <a href="{{ route('meetings.internal.book', ['member' => $member->id]) }}" class="btn btn-sm btn-success text-nowrap">
                            <i class="fas fa-calendar-check"></i> {{ __('portal.meetings.book') }}
                        </a>
                        <button class="btn btn-sm btn-primary text-nowrap" onclick="addSlots({{ $member->id }}, @js($member->name))">
                            <i class="fas fa-plus"></i> {{ __('portal.time_slots.team_slots.add_slots') }}
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Detailed slot list --}}
<div class="card">
    <div class="card-header">
        <h3 class="mb-0">{{ __('portal.time_slots.team_slots.title') }}</h3>
    </div>
    <div class="card-content">
        @if($slots->count() > 0)
        <div class="table-responsive">
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
                    <td><strong>{{ $slot->date->translatedFormat('M d, Y') }}</strong><br><small class="text-muted">{{ $slot->date->translatedFormat('l') }}</small></td>
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
                        @if($slot->status === 'available' && !$slot->isBooked() && !$slot->isPast())
                        <a href="{{ route('meetings.internal.book', ['member' => $slot->user_id]) }}" class="btn btn-sm btn-success" title="{{ __('portal.meetings.book') }}">
                            <i class="fas fa-calendar-check"></i>
                        </a>
                        @endif
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
        </div>
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

{{-- Add-availability modal --}}
<div class="modal fade" id="addSlotsModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-plus"></i> <span id="as_title">{{ __('portal.time_slots.team_slots.add_slots') }}</span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
    </div>
    <form method="POST" action="{{ route('time-slots.store-for-employee') }}">
        @csrf
        <input type="hidden" name="user_id" id="as_user_id">
        <div class="modal-body">
            <div class="row g-2">
                <div class="col-md-4"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.date') }}</label><input type="date" name="date" class="form-control" required min="{{ now()->toDateString() }}"></div>
                <div class="col-md-4"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.start') }}</label><input type="time" name="start_time" class="form-control" required></div>
                <div class="col-md-4"><label class="form-label fw-bold">{{ __('portal.consultations.manager.index.end') }}</label><input type="time" name="end_time" class="form-control" required></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.consultations.manager.index.cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('portal.time_slots.team_slots.add_slots') }}</button>
        </div>
    </form>
</div></div></div>
@endsection

@push('styles')
<style>
.roster-card{display:flex;justify-content:space-between;align-items:center;gap:.75rem;padding:1rem;border:1px solid var(--gray-200);border-radius:var(--radius-md);background:var(--bg-primary);height:100%;}
.roster-info{min-width:0;}
.roster-info strong{display:block;overflow:hidden;text-overflow:ellipsis;}
</style>
@endpush

@push('scripts')
<script>
function toggleBlock(id){fetch(`/internal/time-slots/${id}/toggle-block`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}
function addSlots(userId, name){
    document.getElementById('as_user_id').value = userId;
    document.getElementById('as_title').textContent = @json(__('portal.time_slots.team_slots.add_slots_for')) + ' ' + name;
    new bootstrap.Modal(document.getElementById('addSlotsModal')).show();
}
</script>
@endpush
