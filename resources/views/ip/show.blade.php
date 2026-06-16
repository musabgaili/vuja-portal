@extends('layouts.dashboard')
@section('title', __('portal.ip.registration_details'))
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">📄 {{ $ip->tr('title') }}</h3>
                <span class="status-badge {{ $ip->getStatusBadgeColor() }}">{{ $ip->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="info-section"><h5><i class="fas fa-tag"></i> {{ __('portal.ip.type') }}</h5><p>{{ $ip->ipTypeLabel() }}</p></div>
                <div class="info-section"><h5><i class="fas fa-align-left"></i> {{ __('portal.ip.description') }}</h5><p>{{ $ip->tr('ip_description') }}</p></div>
                @if($ip->registration_number)
                <div class="info-section"><h5><i class="fas fa-certificate"></i> {{ __('portal.ip.registration_number') }}</h5><p class="text-success"><strong>{{ $ip->registration_number }}</strong></p></div>
                @endif
                @if($ip->meeting_confirmed_at)
                <div class="info-section"><h5><i class="fas fa-calendar"></i> {{ __('portal.ip.meeting') }}</h5><p>{{ $ip->meeting_requested_at->translatedFormat('M d, Y g:i A') }}</p>
                @if($ip->meeting_link)<a href="{{ $ip->meeting_link }}" class="btn btn-primary" target="_blank"><i class="fas fa-video"></i> {{ __('portal.meetings.join') }}</a>@endif</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('portal.team.col_actions') }}</h3></div>
            <div class="card-content">
                @if($ip->isSubmitted() && !$ip->meeting_requested_at)
                    @if($ip->assigned_to)
                    <form method="POST" action="{{ route('ip.book-meeting', $ip) }}">@csrf
                        <div class="form-group"><label>{{ __('portal.ip.preferred_date') }}</label><input type="datetime-local" name="meeting_date" class="form-control" required></div>
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-calendar"></i> {{ __('portal.ip.book_meeting') }}</button>
                        <small class="text-muted">{{ __('portal.ip.calendar_integration_coming_soon') }}</small>
                    </form>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> {{ __('portal.ip.consultant_must_be_assigned') }}
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

