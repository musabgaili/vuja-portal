@extends('layouts.internal-dashboard')
@section('title', __('portal.meetings.book_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('meetings.internal.my-meetings') }}">{{ __('portal.meetings.my_meetings') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.meetings.book_title') }}</li>
@endsection

@section('content')
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

@php
    $allowedDurations = [30, 60, 90, 120];
    $specLabel = function ($k) {
        $key = 'targets.cap.spec.'.$k;
        $label = __($key);

        return $label === $key ? ucfirst($k) : $label;
    };
@endphp

<div class="row g-3">
    {{-- ============ Team availability board ============ --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h3 class="mb-0"><i class="fas fa-users"></i> {{ __('portal.meetings.team_availability') }}</h3>
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-secondary" href="{{ route('meetings.internal.book', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}"><i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></a>
                    <span class="btn btn-light disabled">{{ $weekStart->translatedFormat('M j') }} – {{ $weekEnd->translatedFormat('M j') }}</span>
                    <a class="btn btn-outline-secondary" href="{{ route('meetings.internal.book', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}"><i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></a>
                </div>
            </div>
            <div class="card-content">
                <p class="text-muted" style="font-size:.85rem;">{{ __('portal.meetings.board_hint') }}</p>

                @foreach($groups as $specKey => $members)
                    <div class="mb-3">
                        <h6 class="text-uppercase text-muted" style="font-size:.75rem;letter-spacing:.04em;">{{ $specLabel($specKey) }}</h6>
                        @foreach($members as $member)
                            @php $mSlots = $slotsByUser[$member->id] ?? collect(); @endphp
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ $member->name }}</strong>
                                    <span class="badge bg-{{ $mSlots->count() ? 'success' : 'secondary' }}">
                                        {{ trans_choice('portal.meetings.slots_count', $mSlots->count(), ['count' => $mSlots->count()]) }}
                                    </span>
                                </div>
                                @if($mSlots->count())
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        @foreach($mSlots as $slot)
                                            @php
                                                $len = (int) \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time));
                                                $dur = collect($allowedDurations)->filter(fn ($d) => $d <= $len)->last() ?? 30;
                                            @endphp
                                            <button type="button" class="btn btn-sm btn-outline-primary slot-pick"
                                                data-user="{{ $member->id }}"
                                                data-date="{{ $slot->date->toDateString() }}"
                                                data-start="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                                data-dur="{{ $dur }}"
                                                style="font-size:.78rem;">
                                                {{ $slot->date->translatedFormat('D j') }} · {{ $slot->getFormattedTimeRange() }}
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <small class="text-muted">{{ __('portal.meetings.no_slots_this_week') }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ Booking form ============ --}}
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h3 class="mb-0"><i class="fas fa-calendar-plus"></i> {{ __('portal.meetings.new_meeting') }}</h3></div>
            <div class="card-content">
                <form method="POST" action="{{ route('meetings.internal.store') }}" id="bookForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label fw-bold">{{ __('portal.meetings.subject') }} *</label>
                        <input type="text" name="title" class="form-control" required maxlength="255" value="{{ old('title') }}">
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-bold">{{ __('portal.time_slots.date') }} *</label>
                            <input type="date" name="date" id="m_date" class="form-control" required min="{{ now()->toDateString() }}" value="{{ old('date', $prefillDate) }}">
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold">{{ __('portal.meetings.time') }} *</label>
                            <input type="time" name="start_time" id="m_start" class="form-control" required value="{{ old('start_time') }}">
                        </div>
                        <div class="col-3">
                            <label class="form-label fw-bold">{{ __('portal.meetings.duration') }}</label>
                            <select name="duration_minutes" id="m_duration" class="form-control">
                                @foreach($allowedDurations as $d)
                                <option value="{{ $d }}" @selected(old('duration_minutes', 60) == $d)>{{ $d }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-2">
                        <label class="form-label fw-bold">{{ __('portal.meetings.notes_optional') }}</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold d-block">{{ __('portal.meetings.attendees') }} *</label>
                        <p class="text-muted" style="font-size:.8rem;">{{ __('portal.meetings.attendees_hint') }}</p>
                        <div class="border rounded p-2" style="max-height:240px;overflow:auto;">
                            @foreach($groups as $specKey => $members)
                                <div class="text-uppercase text-muted mt-1" style="font-size:.7rem;letter-spacing:.04em;">{{ $specLabel($specKey) }}</div>
                                @foreach($members as $member)
                                    @continue($member->id === $user->id)
                                    @php $cnt = (int) ($upcomingCounts[$member->id] ?? 0); @endphp
                                    <label class="d-flex align-items-center gap-2 py-1" style="font-size:.85rem;cursor:pointer;">
                                        <input type="checkbox" name="attendee_ids[]" value="{{ $member->id }}" class="att-check" data-user="{{ $member->id }}"
                                            @checked(collect(old('attendee_ids', $preselect ? [$preselect] : []))->contains($member->id))>
                                        <span class="flex-grow-1">{{ $member->name }}</span>
                                        @if($cnt)
                                            <span class="badge bg-light text-dark">{{ trans_choice('portal.meetings.slots_count', $cnt, ['count' => $cnt]) }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ __('portal.meetings.no_availability') }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            @endforeach
                        </div>
                    </div>

                    <div class="form-check mt-2">
                        <input type="checkbox" name="include_self" value="1" class="form-check-input" id="incSelf" @checked(old('include_self', true))>
                        <label class="form-check-label" for="incSelf">{{ __('portal.meetings.include_me') }}</label>
                    </div>

                    <div class="alert alert-info mt-3 mb-2" style="font-size:.8rem;">
                        <i class="fas fa-circle-info"></i> {{ __('portal.meetings.flow_explainer') }}
                    </div>

                    <button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane"></i> {{ __('portal.meetings.send_booking') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.slot-pick').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('m_date').value = btn.dataset.date;
        document.getElementById('m_start').value = btn.dataset.start;
        var dur = document.getElementById('m_duration');
        if ([...dur.options].some(o => o.value === btn.dataset.dur)) { dur.value = btn.dataset.dur; }
        var cb = document.querySelector('.att-check[data-user="' + btn.dataset.user + '"]');
        if (cb) { cb.checked = true; }
        document.getElementById('bookForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});
</script>
@endpush
