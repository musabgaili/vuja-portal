@extends('layouts.internal-dashboard')
@section('title', $idea->title)

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('improvement-ideas.manager.index') }}">{{ __('portal.improvement_ideas.manager_title') }}</a></li>
<li class="breadcrumb-item active">{{ Str::limit($idea->title, 40) }}</li>
@endsection

@section('content')
<div class="page-hero d-flex justify-content-between align-items-center flex-wrap gap-2" style="padding:1.5rem 1.75rem; margin-bottom:1.5rem;">
    <div>
        <h1 style="margin:0; font-size:1.35rem;"><i class="fas fa-rocket"></i> {{ $idea->title }}</h1>
        <p style="margin:.25rem 0 0; opacity:.9;">{{ $idea->category }} · {{ __('portal.improvement_ideas.submitted_by') }} {{ $idea->user?->name }} · {{ $idea->created_at->translatedFormat('M d, Y') }}</p>
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
            <div class="card-header"><span class="card-title">{{ __('portal.improvement_ideas.review') }}</span></div>
            <div class="card-content">
                @if($idea->reviewer)
                <p><strong>{{ __('portal.improvement_ideas.reviewed_by') }}:</strong> {{ $idea->reviewer->name }} · {{ optional($idea->reviewed_at)->translatedFormat('M d, Y') }}</p>
                @endif
                @if($idea->review_notes)
                <div class="alert alert-info" data-persist style="white-space:pre-line;">{{ $idea->review_notes }}</div>
                @endif

                @if($idea->isApproved() || $idea->isImplemented())
                <div class="alert alert-success" data-persist><i class="fas fa-bolt"></i> {{ __('portal.improvement_ideas.manager_awarded', ['points' => $points, 'name' => $idea->user?->name]) }}</div>
                @elseif($idea->isRejected())
                <div class="alert alert-secondary" data-persist>{{ __('portal.improvement_ideas.was_rejected') }}</div>
                @else
                {{-- Pending review: manager can approve (awards points) or reject. --}}
                <form method="POST" action="{{ route('improvement-ideas.approve', $idea) }}" class="mb-3">
                    @csrf
                    <label class="form-label fw-bold">{{ __('portal.improvement_ideas.review_notes') }}</label>
                    <textarea name="review_notes" rows="3" class="form-control mb-2" placeholder="{{ __('portal.improvement_ideas.review_notes_ph') }}"></textarea>
                    <button class="btn btn-success w-100"><i class="fas fa-check"></i> {{ __('portal.improvement_ideas.approve_award', ['points' => $points]) }}</button>
                </form>
                <form method="POST" action="{{ route('improvement-ideas.reject', $idea) }}" onsubmit="return confirm(@js(__('portal.improvement_ideas.reject_confirm')))">
                    @csrf
                    <button class="btn btn-outline-danger w-100"><i class="fas fa-times"></i> {{ __('portal.improvement_ideas.reject') }}</button>
                </form>
                @endif
            </div>
        </div>
        <a href="{{ route('improvement-ideas.manager.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> {{ __('portal.improvement_ideas.back_to_queue') }}</a>
    </div>
</div>
@endsection
