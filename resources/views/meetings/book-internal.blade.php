@extends('layouts.internal-dashboard')
@section('title', __('portal.meetings.book_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('meetings.internal.my-meetings') }}">{{ __('portal.meetings.my_meetings') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.meetings.book_title') }}</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header"><h3><i class="fas fa-calendar-plus"></i> {{ __('portal.meetings.book_title') }}</h3></div>
    <div class="card-content">
        <p class="text-muted">{{ __('portal.meetings.book_intro') }}</p>

        <form method="GET" action="{{ route('meetings.internal.book') }}" class="mb-3" style="max-width:420px;">
            <label class="form-label fw-bold">{{ __('portal.meetings.choose_colleague') }}</label>
            <select name="member" class="form-control" onchange="this.form.submit()">
                <option value="">{{ __('portal.meetings.select_colleague') }}</option>
                @foreach($colleagues as $c)
                <option value="{{ $c->id }}" @selected($selected && $selected->id === $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </form>

        @if($selected)
            <hr>
            <h5 style="font-size:1.05rem;">{{ __('portal.meetings.available_slots_for', ['name' => $selected->name]) }}</h5>

            @forelse($slots as $slot)
                @php
                    $slotMin = (int) \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time));
                    $durations = array_values(array_filter([30, 60, 90, 120], fn ($d) => $d <= $slotMin));
                    if (empty($durations)) { $durations = [$slotMin]; }
                @endphp
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $slot->date->translatedFormat('l, M j, Y') }}</strong>
                            <span class="text-muted">· {{ $slot->getFormattedTimeRange() }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#book-{{ $slot->id }}">
                            <i class="fas fa-calendar-check"></i> {{ __('portal.meetings.book') }}
                        </button>
                    </div>
                    <div class="collapse mt-2" id="book-{{ $slot->id }}">
                        <form method="POST" action="{{ route('meetings.internal.store', $slot) }}">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('portal.meetings.subject') }} *</label>
                                    <input type="text" name="title" class="form-control" required maxlength="255">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('portal.meetings.duration') }}</label>
                                    <select name="duration_minutes" class="form-control">
                                        @foreach($durations as $d)
                                        <option value="{{ $d }}">{{ $d }} {{ __('portal.meetings.min') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="form-label">{{ __('portal.meetings.notes_optional') }}</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm mt-2"><i class="fas fa-check"></i> {{ __('portal.meetings.confirm_booking') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="alert alert-info">{{ __('portal.meetings.no_available_slots') }}</div>
            @endforelse

            @if($slots instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $slots->appends(['member' => $selected->id])->links('pagination::bootstrap-5') }}
            @endif

            @if($canCreateSlots)
            <div class="card mt-3" style="background:rgba(15,150,156,.06);border:1px dashed #0F969C;">
                <div class="card-content">
                    <h6 style="font-size:1rem;"><i class="fas fa-plus"></i> {{ __('portal.meetings.add_slot_for', ['name' => $selected->name]) }}</h6>
                    <p class="text-muted" style="font-size:.85rem;">{{ __('portal.meetings.add_slot_hint') }}</p>
                    <form method="POST" action="{{ route('time-slots.store-for-employee') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $selected->id }}">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">{{ __('portal.time_slots.date') }}</label>
                                <input type="date" name="date" class="form-control" required min="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('portal.time_slots.start') }}</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('portal.time_slots.end') }}</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary btn-sm mt-2"><i class="fas fa-plus"></i> {{ __('portal.meetings.add_slot') }}</button>
                    </form>
                </div>
            </div>
            @endif
        @endif
    </div>
</div>
@endsection
