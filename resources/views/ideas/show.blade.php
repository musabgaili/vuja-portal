@extends('layouts.dashboard')

@section('title', 'Idea Request Details')
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
                            <h5><i class="fas fa-{{ $idea->client_type === 'company' ? 'building' : 'user' }}"></i> Client Type</h5>
                            <span class="badge {{ $idea->client_type === 'company' ? 'bg-primary' : 'bg-secondary' }}">
                                {{ ucfirst($idea->client_type) }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="idea-section">
                            <h5><i class="fas fa-info-circle"></i> Idea Status</h5>
                            <span class="badge bg-info">
                                {{ match($idea->idea_status) {
                                    'seeking_around' => 'Seeking Around',
                                    'ready' => 'Ready',
                                    'running_project' => 'Running Project',
                                    'concept_only' => 'Concept Only',
                                    default => ucfirst($idea->idea_status)
                                } }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="idea-section">
                    <h5><i class="fas fa-align-left"></i> Description</h5>
                    <p>{{ $idea->description }}</p>
                </div>

                @if($idea->target_market)
                <div class="idea-section">
                    <h5><i class="fas fa-users"></i> Target Market</h5>
                    <p>{{ $idea->target_market }}</p>
                </div>
                @endif

                @if($idea->problem_solving)
                <div class="idea-section">
                    <h5><i class="fas fa-question-circle"></i> Problem It Solves</h5>
                    <p>{{ $idea->problem_solving }}</p>
                </div>
                @endif

                @if($idea->unique_value)
                <div class="idea-section">
                    <h5><i class="fas fa-star"></i> Unique Value</h5>
                    <p>{{ $idea->unique_value }}</p>
                </div>
                @endif

                @if($idea->final_quote && $idea->quote_status === 'approved')
                <div class="idea-section">
                    <h5><i class="fas fa-dollar-sign"></i> Quote</h5>
                    <div class="quote-box">
                        <div class="quote-amount">${{ number_format($idea->final_quote, 2) }}</div>
                        @if($idea->quote_file_path)
                        <div class="mt-3">
                            <a href="{{ asset('storage/' . $idea->quote_file_path) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> Download Quote Document
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($idea->negotiation_notes)
                <div class="idea-section">
                    <h5><i class="fas fa-comments"></i> Negotiation Notes</h5>
                    <p>{{ $idea->negotiation_notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Available Actions</h3>
            </div>
            <div class="card-content">
                <div class="d-flex gap-2 flex-wrap">
                    @if($idea->isSubmitted() || $idea->isInNegotiation())
                    {{-- AI Assessment button hidden per requirements --}}
                    {{-- <a href="{{ route('ideas.ai-assessment', $idea) }}" class="btn btn-primary">
                        <i class="fas fa-robot"></i> AI Assessment
                    </a> --}}
                    <a href="{{ route('ideas.negotiation', $idea) }}" class="btn btn-secondary">
                        <i class="fas fa-comments"></i> Negotiation
                    </a>
                    @endif

                    @if($idea->isQuoted())
                    <form method="POST" action="{{ route('ideas.accept-quote', $idea) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Accept Quote
                        </button>
                    </form>
                    <button type="button" class="btn btn-error" onclick="showRejectModal()">
                        <i class="fas fa-times"></i> Reject Quote
                    </button>
                    @endif

                    @if($idea->isAccepted() || $idea->isPaymentPending())
                    <a href="{{ route('ideas.payment', $idea) }}" class="btn btn-warning">
                        <i class="fas fa-upload"></i> Upload Payment
                    </a>
                    @endif

                    <a href="{{ route('services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Services
                    </a>
                </div>
            </div>
        </div>

        <!-- Comments/Negotiation History -->
        @if($idea->comments->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Negotiation History</h3>
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
                            <i class="fas fa-tag"></i> Suggested Price: ${{ number_format($comment->suggested_price, 2) }}
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
                <h3 class="card-title">Progress</h3>
            </div>
            <div class="card-content">
                <div class="timeline">
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>Submitted</h6>
                            <small>{{ $idea->created_at->format('M d, Y') }}</small>
                        </div>
                    </div>
                    
                    @if($idea->isInNegotiation() || $idea->isQuoted() || $idea->isAccepted() || $idea->isApproved())
                    <div class="timeline-item active">
                        <div class="timeline-marker warning"></div>
                        <div class="timeline-content">
                            <h6>In Negotiation</h6>
                            <small>Discussing terms</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isQuoted() || $idea->isAccepted() || $idea->isApproved())
                    <div class="timeline-item active">
                        <div class="timeline-marker info"></div>
                        <div class="timeline-content">
                            <h6>Quote Sent</h6>
                            <small>${{ number_format($idea->final_quote, 2) }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isAccepted() || $idea->isPaymentPending() || $idea->isApproved())
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>Quote Accepted</h6>
                            <small>{{ $idea->agreement_accepted_at?->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isPaymentPending() || $idea->isApproved())
                    <div class="timeline-item {{ $idea->isPaymentPending() ? 'active' : '' }}">
                        <div class="timeline-marker warning"></div>
                        <div class="timeline-content">
                            <h6>Payment Pending</h6>
                            <small>Waiting verification</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($idea->isApproved() || $idea->isInProgress() || $idea->isCompleted())
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>Approved</h6>
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
                <h3 class="card-title">Information</h3>
            </div>
            <div class="card-content">
                <div class="info-item">
                    <strong>Status:</strong>
                    <span class="status-badge {{ $idea->getStatusBadgeColor() }}">
                        {{ $idea->getStatusLabel() }}
                    </span>
                </div>
                <div class="info-item">
                    <strong>Submitted:</strong>
                    <span>{{ $idea->created_at->format('M d, Y g:i A') }}</span>
                </div>
                @if($idea->assignedTo)
                <div class="info-item">
                    <strong>Assigned To:</strong>
                    <span>{{ $idea->assignedTo->name }}</span>
                </div>
                @endif
                @if($idea->tokens_used > 0)
                <div class="info-item">
                    <strong>AI Tokens Used:</strong>
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
                <h5>Reject Quote</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('ideas.reject-quote', $idea) }}">
                @csrf
                <div class="modal-body">
                    <p>Please provide a reason for rejecting this quote. This will help us send you a better offer.</p>
                    <div class="form-group">
                        <label>Reason for Rejection *</label>
                        <textarea name="reason" class="form-control" rows="4" required placeholder="e.g., Price is too high, timeline doesn't work, need different approach..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject & Continue Negotiation
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

