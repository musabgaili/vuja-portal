@extends('layouts.dashboard')
@section('title', 'Copyright Details')
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">©️ {{ $copyright->title }}</h3>
                <span class="status-badge {{ $copyright->getStatusBadgeColor() }}">{{ $copyright->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="info-section"><h5><i class="fas fa-tag"></i> Type</h5><p>{{ $copyright->work_type }}</p></div>
                <div class="info-section"><h5><i class="fas fa-align-left"></i> Description</h5><p>{{ $copyright->work_description }}</p></div>
                @if($copyright->copyright_number)
                <div class="info-section"><h5><i class="fas fa-certificate"></i> Copyright Number</h5><p class="text-success"><strong>{{ $copyright->copyright_number }}</strong></p></div>
                @endif
                @if($copyright->meeting_confirmed_at)
                <div class="info-section"><h5><i class="fas fa-calendar"></i> Meeting</h5><p>{{ $copyright->meeting_requested_at->format('M d, Y g:i A') }}</p>
                @if($copyright->meeting_link)<a href="{{ $copyright->meeting_link }}" class="btn btn-primary" target="_blank"><i class="fas fa-video"></i> Join</a>@endif</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Actions</h3></div>
            <div class="card-content">
                @if($copyright->isSubmitted() && !$copyright->meeting_requested_at)
                    @if($copyright->assigned_to)
                    <form method="POST" action="{{ route('copyright.book-meeting', $copyright) }}">@csrf
                        <div class="form-group"><label>Preferred Date</label><input type="datetime-local" name="meeting_date" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-calendar"></i> Book Meeting</button>
                        <small class="text-muted">Calendar integration coming soon</small>
                    </form>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> A consultant must be assigned before you can book a meeting.
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

