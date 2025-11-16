@extends('layouts.dashboard')
@section('title', 'Book Meeting')
@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>Book Meeting</h3>
            </div>
            <div class="card-content">
                <div class="alert alert-info mb-4">
                    <h5>Selected Time Slot</h5>
                    <p class="mb-0">
                        <i class="fas fa-calendar"></i> {{ $timeSlot->date->format('l, F d, Y') }}
                        <br>
                        <i class="fas fa-clock"></i> {{ $timeSlot->getFormattedTimeRange() }}
                        <br>
                        <i class="fas fa-user"></i> With {{ $timeSlot->user->name }}
                    </p>
                </div>

                <form method="POST" action="{{ route('meetings.store', $timeSlot) }}">
                    @csrf
                    
                    <input type="hidden" name="service_request_id" value="{{ $serviceRequestId }}">
                    <input type="hidden" name="service_type" value="{{ $serviceType }}">
                    
                    <div class="form-group">
                        <label>Meeting Title *</label>
                        <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="e.g., Product Consultation">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Description / Agenda</label>
                        <textarea name="description" rows="4" class="form-control" placeholder="What would you like to discuss?">{{ old('description') }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <select name="duration_minutes" class="form-control">
                            <option value="30">30 minutes</option>
                            <option value="60" selected>60 minutes (1 hour)</option>
                            <option value="90">90 minutes (1.5 hours)</option>
                            <option value="120">120 minutes (2 hours)</option>
                        </select>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> You will receive a confirmation email once the team member confirms this meeting.
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Book Meeting
                        </button>
                        <a href="{{ route('meetings.available-slots') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Slots
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

