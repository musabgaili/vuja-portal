@extends('layouts.dashboard')

@section('title', __('portal.consultations.show.title'))
@section('page-title', $consultation->title)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">💬 {{ $consultation->title }}</h3>
                <span class="status-badge {{ $consultation->getStatusBadgeColor() }}">
                    {{ $consultation->getStatusLabel() }}
                </span>
            </div>
            <div class="card-content">
                <div class="info-section">
                    <h5><i class="fas fa-tag"></i> {{ __('portal.consultations.show.category') }}</h5>
                    <p class="category-badge">{{ $consultation->category }}</p>
                </div>

                <div class="info-section">
                    <h5><i class="fas fa-align-left"></i> {{ __('portal.consultations.show.description') }}</h5>
                    <p>{{ $consultation->description }}</p>
                </div>

                @if($consultation->specific_questions)
                <div class="info-section">
                    <h5><i class="fas fa-question-circle"></i> {{ __('portal.consultations.show.specific_questions') }}</h5>
                    <p>{{ $consultation->specific_questions }}</p>
                </div>
                @endif

                @if($consultation->meeting_scheduled_at)
                <div class="info-section">
                    <h5><i class="fas fa-calendar"></i> {{ __('portal.consultations.show.meeting_details') }}</h5>
                    <div class="meeting-box">
                        <div class="meeting-time">
                            <i class="fas fa-clock"></i>
                            {{ $consultation->meeting_scheduled_at->format('l, F d, Y \a\t g:i A') }}
                        </div>
                        @if($consultation->meeting_link)
                        <div class="meeting-link">
                            <a href="{{ $consultation->meeting_link }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-video"></i> {{ __('portal.consultations.show.join_meeting') }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($consultation->meeting_notes)
                <div class="info-section">
                    <h5><i class="fas fa-sticky-note"></i> {{ __('portal.consultations.show.meeting_notes') }}</h5>
                    <div class="notes-box">
                        {{ $consultation->meeting_notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.consultations.show.details') }}</h3>
            </div>
            <div class="card-content">
                <div class="detail-item">
                    <strong>{{ __('portal.consultations.show.status') }}:</strong>
                    <span class="status-badge {{ $consultation->getStatusBadgeColor() }}">
                        {{ $consultation->getStatusLabel() }}
                    </span>
                </div>
                <div class="detail-item">
                    <strong>{{ __('portal.consultations.show.category') }}:</strong>
                    <span>{{ $consultation->category }}</span>
                </div>
                <div class="detail-item">
                    <strong>{{ __('portal.consultations.show.submitted') }}:</strong>
                    <span>{{ $consultation->created_at->format('M d, Y') }}</span>
                </div>
                @if($consultation->assignedTo)
                <div class="detail-item">
                    <strong>{{ __('portal.consultations.show.consultant') }}:</strong>
                    <span>{{ $consultation->assignedTo->name }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.consultations.show.next_steps') }}</h3>
            </div>
            <div class="card-content">
                @if($consultation->isMeetingSent() || $consultation->isMeetingScheduled())
                    <p><i class="fas fa-check text-success"></i> {{ __('portal.consultations.show.meeting_invite_sent') }}</p>
                    <p><i class="fas fa-calendar text-info"></i> {{ __('portal.consultations.show.check_meeting_details_above') }}</p>
                @elseif($consultation->isAssigned())
                    <p><i class="fas fa-clock text-warning"></i> {{ __('portal.consultations.show.consultant_invite_soon') }}</p>
                @else
                    <p><i class="fas fa-filter text-info"></i> {{ __('portal.consultations.show.finding_consultant') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.info-section {
    margin-bottom: var(--space-lg);
    padding-bottom: var(--space-lg);
    border-bottom: 1px solid var(--gray-200);
}

.info-section:last-child {
    border-bottom: none;
}

.info-section h5 {
    color: var(--text-color);
    margin-bottom: var(--space-sm);
}

.info-section h5 i {
    margin-right: var(--space-xs);
    color: var(--primary-color);
}

.category-badge {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: var(--space-xs) var(--space-md);
    border-radius: var(--radius-md);
}

.meeting-box {
    background: var(--bg-tertiary);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
}

.meeting-time {
    font-size: var(--font-size-lg);
    margin-bottom: var(--space-md);
    color: var(--text-color);
}

.meeting-time i {
    color: var(--primary-color);
    margin-right: var(--space-sm);
}

.notes-box {
    background: var(--bg-tertiary);
    padding: var(--space-md);
    border-radius: var(--radius-md);
    white-space: pre-line;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--gray-200);
}

.detail-item:last-child {
    border-bottom: none;
}
</style>
@endpush

