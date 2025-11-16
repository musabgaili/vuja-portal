@extends('layouts.dashboard')

@section('title', 'Service Request Details')
@section('page-title', 'Service Request Details')

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Request Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">{{ $serviceRequest->title }}</h3>
                <div class="d-flex gap-2">
                    <span class="status-badge {{ $serviceRequest->getStatusBadgeColor() }}">
                        {{ ucfirst(str_replace('_', ' ', $serviceRequest->status)) }}
                    </span>
                    <span class="priority-badge {{ $serviceRequest->getPriorityBadgeColor() }}">
                        {{ ucfirst($serviceRequest->priority) }}
                    </span>
                </div>
            </div>
            <div class="card-content">
                <div class="request-meta mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Service Type:</strong> {{ $serviceRequest->getTypeDisplayName() }}
                        </div>
                        <div class="col-md-6">
                            <strong>Submitted:</strong> {{ $serviceRequest->created_at->format('M d, Y \a\t g:i A') }}
                        </div>
                        @if($serviceRequest->assignedTo)
                        <div class="col-md-6">
                            <strong>Assigned To:</strong> {{ $serviceRequest->assignedTo->name }}
                        </div>
                        @endif
                        @if($serviceRequest->reviewedBy)
                        <div class="col-md-6">
                            <strong>Reviewed By:</strong> {{ $serviceRequest->reviewedBy->name }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="request-description">
                    <h5>Description</h5>
                    <p>{{ $serviceRequest->description }}</p>
                </div>

                @if($serviceRequest->requirements)
                <div class="request-requirements">
                    <h5>Requirements</h5>
                    <p>{{ $serviceRequest->requirements }}</p>
                </div>
                @endif

                @if($serviceRequest->budget_range)
                <div class="request-budget">
                    <h5>Budget Range</h5>
                    <p>{{ $serviceRequest->budget_range }}</p>
                </div>
                @endif

                @if($serviceRequest->timeline)
                <div class="request-timeline">
                    <h5>Timeline</h5>
                    <p>{{ $serviceRequest->timeline }}</p>
                </div>
                @endif

                @if($serviceRequest->additional_info)
                <div class="request-additional">
                    <h5>Additional Information</h5>
                    <p>{{ $serviceRequest->additional_info }}</p>
                </div>
                @endif

                @if($serviceRequest->review_notes)
                <div class="request-review-notes">
                    <h5>Review Notes</h5>
                    <div class="alert alert-info">
                        {{ $serviceRequest->review_notes }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-content">
                <div class="d-flex gap-2 flex-wrap">
                    @if($serviceRequest->isPending() && auth()->user()->isClient())
                    <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Request
                    </a>
                    <form method="POST" action="{{ route('service-requests.destroy', $serviceRequest) }}" 
                          onsubmit="return confirm('Are you sure you want to delete this request?')" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">
                            <i class="fas fa-trash"></i> Delete Request
                        </button>
                    </form>
                    @endif

                    @if(auth()->user()->isManager() && $serviceRequest->isPending())
                    <button class="btn btn-success" onclick="showReviewModal('approve')">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="btn btn-error" onclick="showReviewModal('reject')">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    @endif

                    @if(auth()->user()->isManager() && $serviceRequest->isApproved() && !$serviceRequest->assignedTo)
                    <button class="btn btn-primary" onclick="showAssignModal()">
                        <i class="fas fa-user-plus"></i> Assign to Employee
                    </button>
                    @endif

                    <a href="{{ route('service-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Status Timeline</h3>
            </div>
            <div class="card-content">
                <div class="timeline">
                    <div class="timeline-item {{ $serviceRequest->created_at ? 'active' : '' }}">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>Request Submitted</h6>
                            <small class="text-muted">{{ $serviceRequest->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    
                    @if($serviceRequest->reviewed_at)
                    <div class="timeline-item {{ $serviceRequest->isApproved() || $serviceRequest->isRejected() ? 'active' : '' }}">
                        <div class="timeline-marker {{ $serviceRequest->isApproved() ? 'success' : 'error' }}"></div>
                        <div class="timeline-content">
                            <h6>{{ $serviceRequest->isApproved() ? 'Request Approved' : 'Request Rejected' }}</h6>
                            <small class="text-muted">{{ $serviceRequest->reviewed_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($serviceRequest->started_at)
                    <div class="timeline-item {{ $serviceRequest->isInProgress() ? 'active' : '' }}">
                        <div class="timeline-marker primary"></div>
                        <div class="timeline-content">
                            <h6>Work Started</h6>
                            <small class="text-muted">{{ $serviceRequest->started_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($serviceRequest->completed_at)
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>Request Completed</h6>
                            <small class="text-muted">{{ $serviceRequest->completed_at->format('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- External API Alerts -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Available Features</h3>
            </div>
            <div class="card-content">
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Service Request Submission</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Request Review & Approval</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>Task Assignment</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock text-warning"></i>
                        <span>Digital Signatures (External API)</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock text-warning"></i>
                        <span>Calendar Integration (External API)</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock text-warning"></i>
                        <span>AI Assessment Tools (External API)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Modal -->
@if(auth()->user()->isManager())
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Service Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('service-requests.review', $serviceRequest) }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" id="reviewAction">
                    <div class="form-group">
                        <label class="form-label">Review Notes</label>
                        <textarea name="review_notes" rows="4" class="form-control" 
                                  placeholder="Add any notes about your decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="reviewSubmitBtn">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Assign Modal -->
@if(auth()->user()->isManager())
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign to Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('service-requests.assign', $serviceRequest) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select Employee</label>
                        <select name="assigned_to" class="form-control" required>
                            <option value="">Choose an employee...</option>
                            @foreach(\App\Models\User::where('role', 'employee')->get() as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.status-badge, .priority-badge {
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge.success, .priority-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.warning, .priority-badge.warning {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.info, .priority-badge.info {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.error, .priority-badge.error {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.primary, .priority-badge.primary {
    background: #dbeafe;
    color: #1e40af;
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

.timeline-item.active .timeline-marker {
    background: var(--primary-color);
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

.timeline-marker.success {
    background: var(--success-color);
}

.timeline-marker.error {
    background: var(--error-color);
}

.timeline-marker.primary {
    background: var(--primary-color);
}

.feature-item {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    margin-bottom: var(--space-sm);
}

.feature-item i {
    width: 16px;
}

.text-muted {
    color: var(--gray-500);
}

.gap-2 {
    gap: var(--space-sm);
}

.flex-wrap {
    flex-wrap: wrap;
}
</style>
@endpush

@push('scripts')
<script>
function showReviewModal(action) {
    document.getElementById('reviewAction').value = action;
    const submitBtn = document.getElementById('reviewSubmitBtn');
    
    if (action === 'approve') {
        submitBtn.textContent = 'Approve Request';
        submitBtn.className = 'btn btn-success';
    } else {
        submitBtn.textContent = 'Reject Request';
        submitBtn.className = 'btn btn-error';
    }
    
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}

function showAssignModal() {
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}
</script>
@endpush
