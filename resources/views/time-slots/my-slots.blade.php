@extends('layouts.internal-dashboard')
@section('title', __('portal.time_slots.my_slots.page_title'))

@section('breadcrumbs')
<li class="breadcrumb-item active">{{ __('portal.time_slots.my_slots.page_title') }}</li>
@endsection

@section('content')
<style>
.slots-header {
    background: linear-gradient(135deg, #10b981 0%, #2C3F43 100%);
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
.view-toggle .btn.active {
    background: var(--primary-color, #0C7075) !important;
    color: #fff !important;
    border-color: var(--primary-color, #0C7075) !important;
}
#fc { background: #fff; }
.fc .fc-button-primary { background: var(--primary-color, #0C7075); border-color: var(--primary-color, #0C7075); }
.fc .fc-button-primary:not(:disabled).fc-button-active, .fc .fc-button-primary:hover { background: var(--primary-dark, #094f53); border-color: var(--primary-dark, #094f53); }
</style>

<div class="slots-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 style="margin: 0; font-size: 1.75rem; font-weight: 700;"><i class="fas fa-calendar-alt"></i> {{ __('portal.time_slots.my_slots.title') }}</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.95;">{{ __('portal.time_slots.my_slots.subtitle') }}</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="btn-group view-toggle" role="group" aria-label="{{ __('portal.time_slots.my_slots.view_toggle') }}">
                <button type="button" id="btnList" class="btn btn-light active" onclick="spView('list')"><i class="fas fa-list"></i> {{ __('portal.time_slots.my_slots.view_list') }}</button>
                <button type="button" id="btnCal" class="btn btn-light" onclick="spView('calendar')"><i class="fas fa-calendar-days"></i> {{ __('portal.time_slots.my_slots.view_calendar') }}</button>
            </div>
            <a href="{{ route('time-slots.create') }}" class="btn btn-light btn-lg">
                <i class="fas fa-plus"></i> {{ __('portal.time_slots.my_slots.add_availability') }}
            </a>
        </div>
    </div>
</div>

<div id="slotsList">
@if($slots->count() > 0)
<div class="table-slots">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>{{ __('portal.time_slots.my_slots.col_date') }}</th>
                <th>{{ __('portal.time_slots.my_slots.col_time') }}</th>
                <th>{{ __('portal.time_slots.my_slots.col_status') }}</th>
                <th>{{ __('portal.time_slots.my_slots.booked_by') }}</th>
                <th>{{ __('portal.time_slots.my_slots.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($slots as $slot)
            <tr>
                <td>
                    <div class="slot-date">{{ $slot->date->translatedFormat('M d, Y') }}</div>
                    <small class="text-muted">{{ $slot->date->translatedFormat('l') }}</small>
                </td>
                <td>
                    <span class="slot-time">
                        <i class="fas fa-clock"></i> {{ $slot->getFormattedTimeRange() }}
                    </span>
                </td>
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
                        <strong style="color: #1e293b;">{{ $slot->meeting->client->name }}</strong>
                        <br><small class="text-muted"><i class="fas fa-envelope"></i> {{ $slot->meeting->client->email }}</small>
                        @if($slot->meeting->client->phone)
                        <br><small class="text-muted"><i class="fas fa-phone"></i> {{ $slot->meeting->client->phone }}</small>
                        @endif
                        <br><small class="text-info"><i class="fas fa-comment"></i> <strong>{{ $slot->meeting->title }}</strong></small>
                    @else
                        <span class="text-muted">â€”</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        @if(!$slot->isBooked() && !$slot->isPast())
                        <button class="btn btn-sm btn-warning" onclick="toggleBlock({{ $slot->id }})" title="{{ $slot->isBlocked() ? __('portal.time_slots.my_slots.title_unblock') : __('portal.time_slots.my_slots.title_block') }}">
                            <i class="fas fa-{{ $slot->isBlocked() ? 'unlock' : 'lock' }}"></i>
                        </button>
                        <form method="POST" action="{{ route('time-slots.destroy', $slot) }}" style="display:inline;" onsubmit="return confirm(@json(__('portal.time_slots.my_slots.delete_confirm')))">
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
    <h4 style="color: #1e293b; font-weight: 600;">{{ __('portal.time_slots.my_slots.empty_title') }}</h4>
    <p class="text-muted">{{ __('portal.time_slots.my_slots.empty_body') }}</p>
    <a href="{{ route('time-slots.create') }}" class="btn btn-primary btn-lg mt-3">
        <i class="fas fa-plus"></i> {{ __('portal.time_slots.my_slots.add_slots') }}
    </a>
</div>
@endif
</div>{{-- /#slotsList --}}

<div id="slotsCalendar" style="display:none;">
    <div class="table-slots" style="padding:1.25rem;">
        <div id="fc"></div>
        <div class="d-flex gap-3 mt-3 flex-wrap" style="font-size:.8rem;color:#475569;">
            <span><i class="fas fa-square" style="color:#10b981;"></i> {{ __('portal.time_slots.status.available') }}</span>
            <span><i class="fas fa-square" style="color:#0C7075;"></i> {{ __('portal.time_slots.status.booked') }}</span>
            <span><i class="fas fa-square" style="color:#ef4444;"></i> {{ __('portal.time_slots.status.blocked') }}</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
function toggleBlock(id){fetch(`/internal/time-slots/${id}/toggle-block`,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(()=>location.reload());}

var __slotCal = null;
function ensureSlotCalendar() {
    if (__slotCal || typeof FullCalendar === 'undefined') return;
    __slotCal = new FullCalendar.Calendar(document.getElementById('fc'), {
        initialView: 'dayGridMonth',
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,listWeek' },
        locale: @json(app()->getLocale()),
        direction: @json(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'),
        height: 'auto',
        nowIndicator: true,
        events: @json($events),
        eventClick: function (info) {
            var p = info.event.extendedProps, msg = info.event.title;
            if (p.meeting) msg += ' — ' + p.meeting;
            if (p.bookedBy) msg += ' (' + p.bookedBy + ')';
            window.alert(msg);
        }
    });
    __slotCal.render();
}
function spView(v) {
    var isCal = v === 'calendar';
    document.getElementById('slotsList').style.display = isCal ? 'none' : '';
    document.getElementById('slotsCalendar').style.display = isCal ? '' : 'none';
    document.getElementById('btnList').classList.toggle('active', !isCal);
    document.getElementById('btnCal').classList.toggle('active', isCal);
    try { localStorage.setItem('vd-slots-view', v); } catch (e) {}
    if (isCal) { ensureSlotCalendar(); if (__slotCal) setTimeout(function () { __slotCal.updateSize(); }, 30); }
}
document.addEventListener('DOMContentLoaded', function () {
    var saved = 'list';
    try { saved = localStorage.getItem('vd-slots-view') || 'list'; } catch (e) {}
    spView(saved);
});
</script>
@endpush
