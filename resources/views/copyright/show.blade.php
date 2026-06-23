@extends('layouts.dashboard')
@section('title', __('portal.copyright.show.title'))
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">©️ {{ $copyright->tr('title') }}</h3>
                <span class="status-badge {{ $copyright->getStatusBadgeColor() }}">{{ $copyright->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="info-section"><h5><i class="fas fa-tag"></i> {{ __('portal.copyright.show.type') }}</h5><p>{{ $copyright->workTypeLabel() }}</p></div>
                <div class="info-section"><h5><i class="fas fa-align-left"></i> {{ __('portal.copyright.show.description') }}</h5><p>{{ $copyright->tr('work_description') }}</p></div>
                @if($copyright->copyright_number)
                <div class="info-section"><h5><i class="fas fa-certificate"></i> {{ __('portal.copyright.show.copyright_number') }}</h5><p class="text-success"><strong>{{ $copyright->copyright_number }}</strong></p></div>
                @endif
                @if($copyright->meeting_confirmed_at)
                <div class="info-section"><h5><i class="fas fa-calendar"></i> {{ __('portal.copyright.show.meeting') }}</h5><p>{{ $copyright->meeting_requested_at->translatedFormat('M d, Y g:i A') }}</p>
                @if($copyright->meeting_link)<a href="{{ $copyright->meeting_link }}" class="btn btn-primary" target="_blank"><i class="fas fa-video"></i> {{ __('portal.meetings.join') }}</a>@endif</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('portal.copyright.show.actions') }}</h3></div>
            <div class="card-content">
                @if($copyright->isSubmitted() && !$copyright->meeting_requested_at)
                    @if($copyright->assigned_to)
                    <form method="POST" action="{{ route('copyright.book-meeting', $copyright) }}">@csrf
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

@include('partials.client-deliverables', ['model' => $copyright])
@endsection

