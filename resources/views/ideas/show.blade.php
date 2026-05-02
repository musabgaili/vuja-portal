@extends('layouts.dashboard')

@section('title', __('portal.ideas.show.page_title'))
@section('page-title', $idea->title)

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Idea Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">💡 {{ $idea->title }}</h3>
                <span class="status-badge {{ $idea->getStatusBadgeColor() }}">
                    {{ $idea->getStatusLabel() }}
                </span>
            </div>
            <div class="card-content">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="idea-section">
                            <h5><i class="fas fa-{{ $idea->client_type === 'company' ? 'building' : 'user' }}"></i> {{ __('portal.ideas.show.client_type') }}</h5>
                            <span class="badge {{ $idea->client_type === 'company' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ __('portal.ideas.client_type.'.$idea->client_type) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="idea-section">
                            <h5><i class="fas fa-info-circle"></i> {{ __('portal.ideas.show.idea_status') }}</h5>
                            <span class="badge bg-info">
                                {{ __('portal.ideas.idea_status.'.$idea->idea_status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="idea-section">
                    <h5><i class="fas fa-align-left"></i> {{ __('portal.ideas.show.description') }}</h5>
                    <p>{{ $idea->description }}</p>
                </div>

                @if($idea->target_market)
                <div class="idea-section">
                    <h5><i class="fas fa-users"></i> {{ __('portal.ideas.show.target_market') }}</h5>
                    <p>{{ $idea->target_market }}</p>
                </div>
                @endif

                @if($idea->problem_solving)
                <div class="idea-section">
                    <h5><i class="fas fa-question-circle"></i> {{ __('portal.ideas.show.problem_solves') }}</h5>
                    <p>{{ $idea->problem_solving }}</p>
                </div>
                @endif

                @if($idea->unique_value)
                <div class="idea-section">
                    <h5><i class="fas fa-star"></i> {{ __('portal.ideas.show.unique_value') }}</h5>
                    <p>{{ $idea->unique_value }}</p>
                </div>
                @endif

                @if($idea->final_quote && $idea->quote_status === 'approved')
                <div class="idea-section">
                    <h5><i class="fas fa-dollar-sign"></i> {{ __('portal.ideas.show.quote') }}</h5>
                    <div class="quote-box">
                        <div class="quote-amount">${{ number_format($idea->final_quote, 2) }}</div>
                        @if($idea->quote_file_path)
                        <div class="mt-3">
                            <a href="{{ asset('storage/' . $idea->quote_file_path) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> {{ __('portal.ideas.show.download_quote') }}
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($idea->negotiation_notes)
                <div class="idea-section">
                    <h5><i class="fas fa-comments"></i> {{ __('portal.ideas.show.negotiation_notes') }}</h5>
                    <p>{{ $idea->negotiation_notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.ideas.show.actions') }}</h3>
            </div>
            <div class="card-content">
                <div class="d-flex gap-2 flex-wrap">
                    @if($idea->isSubmitted() || $idea->isInNegotiation())
                    <a href="{{ route('ideas.negotiation', $idea) }}" class="btn btn-secondary">
                        <i class="fas fa-comments"></i> {{ __('portal.ideas.show.negotiation') }}
                    </a>
                    @endif

                    @if($idea->isQuoted())
                    <form method="POST" action="{{ route('ideas.accept-quote', $idea) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> {{ __('portal.ideas.show.accept_quote') }}
                        </button>
                    </form>
                    <button type="button" class="btn btn-error" onclick="showRejectModal()">
                        <i class="fas fa-times"></i> {{ __('portal.ideas.show.reject_quote') }}
                    </button>
                    @endif

                    @if($idea->isAccepted() || $idea->isPaymentPending())
                    <a href="{{ route('ideas.payment', $idea) }}" class="btn btn-warning">
                        <i class="fas fa-upload"></i> {{ __('portal.ideas.show.upload_payment') }}
                    </a>
                    @endif

                    <a href="{{ route('services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('portal.ideas.show.back_services') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Comments/Negotiation History -->
        @if($idea->comments->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.ideas.show.history') }}</h3>
            </div>
            <div class="card-content">
                @foreach($idea->comments as $comment)
                <div class="comment-item {{ $comment->is_internal ? 'internal' : 'client' }}">
                    <div class="comment-header">
                        <strong>{{ $comment->user->name }}</strong>
                        <span class="comment-time">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="comment-body">
                        {{ $comment->comment }}
                        @if($comment->suggested_price)
                        <div class="suggested-price">
                            <i class="fas fa-tag"></i> {{ __('portal.ideas.show.suggested_price') }} ${{ number_format($comment->suggested_price, 2) }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.ideas.show.progress') }}</h3>
            </div>
            <div class="card-content">
                <div class="timeline">
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.ideas.show.timeline_submitted') }}</h6>
                            <small>{{ $idea->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    
                    @if($idea->isInNegotiation() || $idea->isQuoted() || $idea->isAccepted() || $idea->isApproved())
                    <div class="timeline-item active">
                        <div class="timeline-marker warning"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.ideas.show.timeline_in_negotiation') }}</h6>
                            <small>{{ __('portal.ideas.show.timeline_in_negotiation_sub') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isQuoted() || $idea->isAccepted() || $idea->isApproved())
                    <div class="timeline-item active">
                        <div class="timeline-marker info"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.ideas.show.timeline_quote_sent') }}</h6>
                            <small>${{ number_format($idea->final_quote, 2) }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isAccepted() || $idea->isPaymentPending() || $idea->isApproved())
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.ideas.show.timeline_quote_accepted') }}</h6>
                            <small>{{ $idea->agreement_accepted_at?->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isPaymentPending() || $idea->isApproved())
                    <div class="timeline-item {{ $idea->isPaymentPending() ? 'active' : '' }}">
                        <div class="timeline-marker warning"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.ideas.show.payment_pending') }}</h6>
                            <small>{{ __('portal.ideas.show.payment_pending_sub') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isApproved() || $idea->isInProgress() || $idea->isCompleted())
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.ideas.show.timeline_approved') }}</h6>
                            <small>{{ $idea->payment_verified_at?->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.ideas.show.information') }}</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <strong>{{ __('portal.ideas.show.status') }}</strong>
                    <span class="status-badge {{ $idea->getStatusBadgeColor() }}">
                        {{ $idea->getStatusLabel() }}
                    </span>
                </div>
                <div class="info-item">
                    <strong>{{ __('portal.ideas.show.submitted') }}</strong>
                    <span>{{ $idea->created_at->format('M d, Y g:i A') }}</span>
                </div>
                @if($idea->assignedTo)
                <div class="info-item">
                    <strong>{{ __('portal.ideas.show.assigned_to') }}</strong>
                    <span>{{ $idea->assignedTo->name }}</span>
                </div>
                @endif
                @if($idea->tokens_used > 0)
                <div class="info-item">
                    <strong>{{ __('portal.ideas.show.ai_tokens_used') }}</strong>
                    <span>{{ $idea->tokens_used }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.idea-section {
    margin-bottom: var(--space-lg);
    padding-bottom: var(--space-lg);
    border-bottom: 1px solid var(--gray-200);
}

.idea-section:last-child {
    border-bottom: none;
}

.idea-section h5 {
    color: var(--text-color);
    margin-bottom: var(--space-sm);
}

.idea-section h5 i {
    margin-right: var(--space-xs);
    color: var(--primary-color);
}

.quote-box {
    background: var(--bg-tertiary);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
}

.quote-amount {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: var(--space-sm);
}

.quote-terms {
    margin-top: var(--space-md);
}

.comment-item {
    background: var(--bg-tertiary);
    padding: var(--space-md);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-sm);
    border-left: 3px solid var(--primary-color);
}

.comment-item.internal {
    border-left-color: var(--warning-color);
    background: #fef3c7;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--space-xs);
}

.comment-time {
    color: var(--gray-500);
    font-size: var(--font-size-sm);
}

.suggested-price {
    margin-top: var(--space-sm);
    padding: var(--space-sm);
    background: white;
    border-radius: var(--radius-sm);
    color: var(--success-color);
    font-weight: 600;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--gray-300);
}

.timeline-item {
    position: relative;
    margin-bottom: var(--space-lg);
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--gray-300);
    border: 2px solid white;
}

.timeline-item.active .timeline-marker {
    background: var(--primary-color);
}

.timeline-marker.success { background: var(--success-color); }
.timeline-marker.warning { background: var(--warning-color); }
.timeline-marker.info { background: var(--info-color); }

.info-item {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--gray-200);
}

.info-item:last-child {
    border-bottom: none;
}
</style>
@endpush

<div class="modal fade" id="rejectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>{{ __('portal.ideas.show.reject_modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('ideas.reject-quote', $idea) }}">
                @csrf
                <div class="modal-body">
                    <p>{{ __('portal.ideas.show.reject_modal_body') }}</p>
                    <div class="form-group">
                        <label>{{ __('portal.ideas.show.reject_reason_label') }}</label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="{{ __('portal.ideas.show.reject_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.ideas.show.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> {{ __('portal.ideas.show.reject_submit') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endpush
