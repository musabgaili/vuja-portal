@extends(auth()->user()?->isInternal() ? 'layouts.internal-dashboard' : 'layouts.dashboard')
@section('title', $idea->title)

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <div>
        <h1 style="margin:0; font-size:1.35rem;"><i class="fas fa-rocket"></i> {{ $idea->title }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ $idea->category }} · {{ $idea->created_at->format('M d, Y') }}</p>
    </div>
    <span class="badge bg-{{ $idea->getStatusBadgeColor() }}" style="font-size:.9rem;">{{ $idea->getStatusLabel() }}</span>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">{{ __('portal.improvement_ideas.field_description') }}</span></div>
            <div class="card-content"><p style="white-space:pre-line; margin:0;">{{ $idea->description }}</p></div>
        </div>
        @if($idea->technology_used)
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">{{ __('portal.improvement_ideas.field_technology') }}</span></div>
            <div class="card-content"><p style="white-space:pre-line; margin:0;">{{ $idea->technology_used }}</p></div>
        </div>
        @endif
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('portal.improvement_ideas.field_benefit') }}</span></div>
            <div class="card-content"><p style="white-space:pre-line; margin:0;">{{ $idea->benefit }}</p></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><span class="card-title">{{ __('portal.improvement_ideas.status_card') }}</span></div>
            <div class="card-content">
                <p><strong>{{ __('portal.improvement_ideas.col_status') }}:</strong> <span class="badge bg-{{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span></p>
                <p><strong>{{ __('portal.improvement_ideas.submitted_by') }}:</strong> {{ $idea->user?->name }}</p>
                @if($idea->reviewer)
                <p class="mb-0"><strong>{{ __('portal.improvement_ideas.reviewed_by') }}:</strong> {{ $idea->reviewer->name }} · {{ optional($idea->reviewed_at)->format('M d, Y') }}</p>
                @endif
                @if($idea->review_notes)
                <div class="alert alert-info mt-3" data-persist><strong>{{ __('portal.improvement_ideas.review_notes') }}:</strong><br>{{ $idea->review_notes }}</div>
                @endif
                @if($idea->isApproved() || $idea->isImplemented())
                <div class="alert alert-success mt-3" data-persist><i class="fas fa-bolt"></i> {{ __('portal.improvement_ideas.approved_points') }}</div>
                @endif
            </div>
        </div>
        <a href="{{ route('improvement-ideas.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.improvement_ideas.back') }}</a>
    </div>
</div>
@endsection
