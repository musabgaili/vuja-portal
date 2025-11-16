@extends('layouts.dashboard')
@section('title', 'IP Registration Details')
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📄 {{ $ip->title }}</h3>
                <span class="status-badge {{ $ip->getStatusBadgeColor() }}">{{ $ip->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="info-section"><h5><i class="fas fa-tag"></i> Type</h5><p>{{ $ip->ip_type }}</p></div>
                <div class="info-section"><h5><i class="fas fa-align-left"></i> Description</h5><p>{{ $ip->ip_description }}</p></div>
                @if($ip->registration_number)
                <div class="info-section"><h5><i class="fas fa-certificate"></i> Registration Number</h5><p class="text-success"><strong>{{ $ip->registration_number }}</strong></p></div>
                @endif
                @if($ip->meeting_confirmed_at)
                <div class="info-section"><h5><i class="fas fa-calendar"></i> Meeting</h5><p>{{ $ip->meeting_requested_at->format('M d, Y g:i A') }}</p>
                @if($ip->meeting_link)<a href="{{ $ip->meeting_link }}" class="btn btn-primary" target="_blank"><i class="fas fa-video"></i> Join</a>@endif</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Actions</h3></div>
            <div class="card-content">
                @if($ip->isSubmitted() && !$ip->meeting_requested_at)
                    @if($ip->assigned_to)
                    <form method="POST" action="{{ route('ip.book-meeting', $ip) }}">@csrf
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

