@extends('layouts.dashboard')

@section('title', __('portal.service_requests_page.show.page_title'))
@section('page-title', __('portal.service_requests_page.show.page_heading'))

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
                        {{ __('portal.service_requests_page.index.status_' . $serviceRequest->status) }}
                    </span>
                    <span class="priority-badge {{ $serviceRequest->getPriorityBadgeColor() }}">
                        {{ __('portal.service_requests_page.priority.' . $serviceRequest->priority) }}
                    </span>
                </div>
            </div>
            <div class="card-content">
                <div class="request-meta mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>{{ __('portal.service_requests_page.show.service_type') }}</strong> {{ $serviceRequest->getTypeDisplayName() }}
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('portal.service_requests_page.show.submitted') }}</strong> {{ $serviceRequest->created_at->translatedFormat('M d, Y \a\t g:i A') }}
                        </div>
                        @if($serviceRequest->assignedTo)
                        <div class="col-md-6">
                            <strong>{{ __('portal.service_requests_page.show.assigned_to') }}</strong> {{ $serviceRequest->assignedTo->name }}
                        </div>
                        @endif
                        @if($serviceRequest->reviewedBy)
                        <div class="col-md-6">
                            <strong>{{ __('portal.service_requests_page.show.reviewed_by') }}</strong> {{ $serviceRequest->reviewedBy->name }}
                        </div>
                        @endif
                    </div>
                </div>

                <div class="request-description">
                    <h5>{{ __('portal.service_requests_page.show.description') }}</h5>
                    <p>{{ $serviceRequest->description }}</p>
                </div>

                @if($serviceRequest->requirements)
                <div class="request-requirements">
                    <h5>{{ __('portal.service_requests_page.show.requirements') }}</h5>
                    <p>{{ $serviceRequest->requirements }}</p>
                </div>
                @endif

                @if($serviceRequest->budget_range)
                <div class="request-budget">
                    <h5>{{ __('portal.service_requests_page.show.budget_range') }}</h5>
                    <p>{{ $serviceRequest->budget_range }}</p>
                </div>
                @endif

                @if($serviceRequest->timeline)
                <div class="request-timeline">
                    <h5>{{ __('portal.service_requests_page.show.timeline') }}</h5>
                    <p>{{ $serviceRequest->timeline }}</p>
                </div>
                @endif

                @if($serviceRequest->additional_info)
                <div class="request-additional">
                    <h5>{{ __('portal.service_requests_page.show.additional_information') }}</h5>
                    <p>{{ $serviceRequest->additional_info }}</p>
                </div>
                @endif

                @if($serviceRequest->review_notes)
                <div class="request-review-notes">
                    <h5>{{ __('portal.service_requests_page.show.review_notes') }}</h5>
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
                <h3 class="card-title">{{ __('portal.service_requests_page.show.actions') }}</h3>
            </div>
            <div class="card-content">
                <div class="d-flex gap-2 flex-wrap">
                    @if($serviceRequest->isPending() && auth()->user()->isClient())
                    <a href="{{ route('service-requests.edit', $serviceRequest) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> {{ __('portal.service_requests_page.show.edit_request') }}
                    </a>
                    <form method="POST" action="{{ route('service-requests.destroy', $serviceRequest) }}" 
                          onsubmit="return confirm(@json(__('portal.service_requests_page.delete_confirm')))" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error">
                            <i class="fas fa-trash"></i> {{ __('portal.service_requests_page.show.delete_request') }}
                        </button>
                    </form>
                    @endif

                    @if(auth()->user()->isManager() && $serviceRequest->isPending())
                    <button class="btn btn-success" onclick="showReviewModal('approve')">
                        <i class="fas fa-check"></i> {{ __('portal.service_requests_page.show.approve') }}
                    </button>
                    <button class="btn btn-error" onclick="showReviewModal('reject')">
                        <i class="fas fa-times"></i> {{ __('portal.service_requests_page.show.reject') }}
                    </button>
                    @endif

                    @if(auth()->user()->isManager() && $serviceRequest->isApproved() && !$serviceRequest->assignedTo)
                    <button class="btn btn-primary" onclick="showAssignModal()">
                        <i class="fas fa-user-plus"></i> {{ __('portal.service_requests_page.show.assign_employee') }}
                    </button>
                    @endif

                    <a href="{{ route('service-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> {{ __('portal.service_requests_page.show.back_requests') }}
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
                <h3 class="card-title">{{ __('portal.service_requests_page.show.status_timeline') }}</h3>
            </div>
            <div class="card-content">
                <div class="timeline">
                    <div class="timeline-item {{ $serviceRequest->created_at ? 'active' : '' }}">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.service_requests_page.show.timeline_submitted') }}</h6>
                            <small class="text-muted">{{ $serviceRequest->created_at->translatedFormat('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    
                    @if($serviceRequest->reviewed_at)
                    <div class="timeline-item {{ $serviceRequest->isApproved() || $serviceRequest->isRejected() ? 'active' : '' }}">
                        <div class="timeline-marker {{ $serviceRequest->isApproved() ? 'success' : 'error' }}"></div>
                        <div class="timeline-content">
                            <h6>{{ $serviceRequest->isApproved() ? __('portal.service_requests_page.show.timeline_approved') : __('portal.service_requests_page.show.timeline_rejected') }}</h6>
                            <small class="text-muted">{{ $serviceRequest->reviewed_at->translatedFormat('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($serviceRequest->started_at)
                    <div class="timeline-item {{ $serviceRequest->isInProgress() ? 'active' : '' }}">
                        <div class="timeline-marker primary"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.service_requests_page.show.timeline_started') }}</h6>
                            <small class="text-muted">{{ $serviceRequest->started_at->translatedFormat('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($serviceRequest->completed_at)
                    <div class="timeline-item active">
                        <div class="timeline-marker success"></div>
                        <div class="timeline-content">
                            <h6>{{ __('portal.service_requests_page.show.timeline_completed') }}</h6>
                            <small class="text-muted">{{ $serviceRequest->completed_at->translatedFormat('M d, Y g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- External API Alerts -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.service_requests_page.available_features') }}</h3>
            </div>
            <div class="card-content">
                <div class="feature-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>{{ __('portal.service_requests_page.feature_submission') }}</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>{{ __('portal.service_requests_page.feature_review') }}</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle text-success"></i>
                        <span>{{ __('portal.service_requests_page.feature_tasks') }}</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock text-warning"></i>
                        <span>{{ __('portal.service_requests_page.feature_signatures') }}</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock text-warning"></i>
                        <span>{{ __('portal.service_requests_page.feature_calendar') }}</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock text-warning"></i>
                        <span>{{ __('portal.service_requests_page.feature_ai') }}</span>
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
                <h5 class="modal-title">{{ __('portal.service_requests_page.show.review_modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('service-requests.review', $serviceRequest) }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" id="reviewAction">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.service_requests_page.show.review_notes_label') }}</label>
                        <textarea name="review_notes" rows="4" class="form-control" 
                                  placeholder="{{ __('portal.service_requests_page.show.review_notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.service_requests_page.cancel') }}</button>
                    <button type="submit" class="btn" id="reviewSubmitBtn">{{ __('portal.service_requests_page.show.review_submit') }}</button>
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
                <h5 class="modal-title">{{ __('portal.service_requests_page.assign_modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('service-requests.assign', $serviceRequest) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.service_requests_page.assign_select_employee') }}</label>
                        <select name="assigned_to" class="form-control" required>
                            <option value="">{{ __('portal.service_requests_page.assign_choose_employee') }}</option>
                            @foreach(\App\Models\User::where('role', 'employee')->get() as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.service_requests_page.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('portal.service_requests_page.assign_submit') }}</button>
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
const serviceRequestShowLabels = {
    approveRequest: @json(__('portal.service_requests_page.show.approve_request')),
    rejectRequest: @json(__('portal.service_requests_page.show.reject_request')),
};

function showReviewModal(action) {
    document.getElementById('reviewAction').value = action;
    const submitBtn = document.getElementById('reviewSubmitBtn');
    
    if (action === 'approve') {
        submitBtn.textContent = serviceRequestShowLabels.approveRequest;
        submitBtn.className = 'btn btn-success';
    } else {
        submitBtn.textContent = serviceRequestShowLabels.rejectRequest;
        submitBtn.className = 'btn btn-error';
    }
    
    new bootstrap.Modal(document.getElementById('reviewModal')).show();
}

function showAssignModal() {
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}
</script>
@endpush
