@extends('layouts.dashboard')
@section('title', __('portal.research.show.page_title'))
@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">🔍 {{ $research->tr('title') }}</h3>
                <span class="status-badge {{ $research->getStatusBadgeColor() }}">{{ $research->getStatusLabel() }}</span>
            </div>
            <div class="card-content">
                <div class="info-section">
                    <h5><i class="fas fa-search"></i> {{ __('portal.research.show.research_topic') }}</h5>
                    <p>{{ $research->tr('research_topic') }}</p>
                </div>
                @if($research->research_details)
                <div class="info-section">
                    <h5><i class="fas fa-align-left"></i> {{ __('portal.research.show.details') }}</h5>
                    <p>{{ $research->tr('research_details') }}</p>
                </div>
                @endif
                @if($research->relevant_links)
                <div class="info-section">
                    <h5><i class="fas fa-link"></i> {{ __('portal.research.show.links') }}</h5>
                    <p style="white-space: pre-line;">{{ $research->relevant_links }}</p>
                </div>
                @endif
                @if($research->research_findings)
                <div class="info-section">
                    <h5><i class="fas fa-file-alt"></i> {{ __('portal.research.show.findings') }}</h5>
                    <div class="findings-box">{{ $research->research_findings }}</div>
                </div>
                @endif
                @if($research->meeting_scheduled_at)
                <div class="info-section">
                    <h5><i class="fas fa-calendar"></i> {{ __('portal.research.show.meeting') }}</h5>
                    <p>{{ $research->meeting_scheduled_at->translatedFormat('l, F d, Y \a\t g:i A') }}</p>
                    @if($research->meeting_link)
                    <a href="{{ $research->meeting_link }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-video"></i> {{ __('portal.research.show.join_meeting') }}
                    </a>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">{{ __('portal.research.show.actions') }}</h3></div>
            <div class="card-content">
                @if($research->isNdaPending() || $research->isSubmitted())
                <form method="POST" action="{{ route('research.sign-documents', $research) }}">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-block mb-2">
                        <i class="fas fa-file-signature"></i> {{ __('portal.research.show.sign_nda') }}
                    </button>
                    <small class="text-muted">{{ __('portal.research.show.signature_soon') }}</small>
                </form>
                @endif
                @if($research->isNdaSigned() && !$research->meeting_scheduled_at)
                    @if($research->assigned_to)
                    <form method="POST" action="{{ route('research.book-meeting', $research) }}">
                        @csrf
                        <div class="form-group">
                            <label>{{ __('portal.research.show.preferred_date') }}</label>
                            <input type="datetime-local" name="preferred_date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar"></i> {{ __('portal.research.show.book_meeting') }}
                        </button>
                        <small class="text-muted">{{ __('portal.research.show.calendar_soon') }}</small>
                    </form>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> {{ __('portal.research.show.consultant_required') }}
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@include('partials.client-deliverables', ['model' => $research])
@endsection
