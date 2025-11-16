@extends('layouts.dashboard')
@section('title', 'Research Request Details')
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🔍 {{ $research->title }}</h3>
                <span class="status-badge {{ $research->getStatusBadgeColor() }}">{{ $research->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="info-section">
                    <h5><i class="fas fa-search"></i> Research Topic</h5>
                    <p>{{ $research->research_topic }}</p>
                </div>
                @if($research->research_details)
                <div class="info-section">
                    <h5><i class="fas fa-align-left"></i> Details</h5>
                    <p>{{ $research->research_details }}</p>
                </div>
                @endif
                @if($research->relevant_links)
                <div class="info-section">
                    <h5><i class="fas fa-link"></i> Links</h5>
                    <p style="white-space: pre-line;">{{ $research->relevant_links }}</p>
                </div>
                @endif
                @if($research->research_findings)
                <div class="info-section">
                    <h5><i class="fas fa-file-alt"></i> Research Findings</h5>
                    <div class="findings-box">{{ $research->research_findings }}</div>
                </div>
                @endif
                @if($research->meeting_scheduled_at)
                <div class="info-section">
                    <h5><i class="fas fa-calendar"></i> Meeting</h5>
                    <p>{{ $research->meeting_scheduled_at->format('l, F d, Y \a\t g:i A') }}</p>
                    @if($research->meeting_link)
                    <a href="{{ $research->meeting_link }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-video"></i> Join Meeting
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Actions</h3></div>
            <div class="card-content">
                @if($research->isNdaPending() || $research->isSubmitted())
                <form method="POST" action="{{ route('research.sign-documents', $research) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-file-signature"></i> Sign NDA/SLA
                    </button>
                    <small class="text-muted">Digital signature integration coming soon</small>
                </form>
                @endif
                @if($research->isNdaSigned() && !$research->meeting_scheduled_at)
                    @if($research->assigned_to)
                    <form method="POST" action="{{ route('research.book-meeting', $research) }}">
                        @csrf
                        <div class="form-group">
                            <label>Preferred Date</label>
                            <input type="datetime-local" name="preferred_date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar"></i> Book Meeting
                        </button>
                        <small class="text-muted">Calendar API integration coming soon</small>
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

