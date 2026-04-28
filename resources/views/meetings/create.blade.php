@extends('layouts.dashboard')
@section('title', __('portal.meetings.book_meeting'))
@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>{{ __('portal.meetings.book_meeting') }}</h3>
            </div>
            <div class="card-content">
                <div class="alert alert-info mb-4">
                    <h5>{{ __('portal.meetings.selected_time_slot') }}</h5>
                    <p class="mb-0">
                        <i class="fas fa-calendar"></i> {{ $timeSlot->date->format('l, F d, Y') }}
                        <br>
                        <i class="fas fa-clock"></i> {{ $timeSlot->getFormattedTimeRange() }}
                        <br>
                        <i class="fas fa-user"></i> {{ __('portal.meetings.with') }} {{ $timeSlot->user->name }}
                    </p>
                </div>

                <form method="POST" action="{{ route('meetings.store', $timeSlot) }}">
                    @csrf
                    
                    <input type="hidden" name="service_request_id" value="{{ $serviceRequestId }}">
                    <input type="hidden" name="service_type" value="{{ $serviceType }}">
                    
                    <div class="form-group">
                        <label>{{ __('portal.meetings.meeting_title') }} *</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="{{ __('portal.meetings.meeting_title_placeholder') }}">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.meetings.description_agenda') }}</label>
                        <textarea name="description" rows="4" class="form-control" placeholder="{{ __('portal.meetings.description_placeholder') }}">{{ old('description') }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.meetings.duration_minutes') }}</label>
                        <select name="duration_minutes" class="form-control">
                            <option value="30">{{ __('portal.meetings.duration_30') }}</option>
                            <option value="60" selected>{{ __('portal.meetings.duration_60') }}</option>
                            <option value="90">{{ __('portal.meetings.duration_90') }}</option>
                            <option value="120">{{ __('portal.meetings.duration_120') }}</option>
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ __('portal.team.note_label') }}</strong> {{ __('portal.meetings.confirmation_email_note') }}
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> {{ __('portal.meetings.book_meeting') }}
                        </button>
                        <a href="{{ route('meetings.available-slots') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('portal.meetings.back_to_slots') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

