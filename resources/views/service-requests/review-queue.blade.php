@extends('layouts.internal-dashboard')

@section('title', __('portal.service_requests_page.review_queue.page_title'))
@section('page-title', __('portal.service_requests_page.review_queue.page_heading'))

@section('content')
<!-- Review Queue Stats -->
<div class="dashboard-grid mb-4">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.service_requests_page.review_queue.pending_title') }}</h3>
            <div class="widget-icon warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $requests->total() }}</span>
                    <span class="stat-label">{{ __('portal.service_requests_page.review_queue.total') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.service_requests_page.review_queue.this_week') }}</h3>
            <div class="widget-icon info">
                <i class="fas fa-calendar-week"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $requests->where('created_at', '>=', now()->startOfWeek())->count() }}</span>
                    <span class="stat-label">{{ __('portal.service_requests_page.review_queue.new_label') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.service_requests_page.review_queue.urgent_priority') }}</h3>
            <div class="widget-icon error">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $requests->where('priority', 'urgent')->count() }}</span>
                    <span class="stat-label">{{ __('portal.service_requests_page.review_queue.urgent_stat') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Queue -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.service_requests_page.review_queue.list_title') }}</h3>
        <div class="d-flex gap-2">
            <select class="form-control" style="width: auto;" onchange="filterByPriority(this.value)">
                <option value="">{{ __('portal.service_requests_page.review_queue.all_priorities') }}</option>
                <option value="urgent">{{ __('portal.service_requests_page.priority.urgent') }}</option>
                <option value="high">{{ __('portal.service_requests_page.priority.high') }}</option>
                <option value="medium">{{ __('portal.service_requests_page.priority.medium') }}</option>
                <option value="low">{{ __('portal.service_requests_page.priority.low') }}</option>
            </select>
            <select class="form-control" style="width: auto;" onchange="filterByType(this.value)">
                <option value="">{{ __('portal.service_requests_page.review_queue.all_types') }}</option>
                <option value="idea">{{ __('portal.service_requests_page.create.type_idea') }}</option>
                <option value="consultation">{{ __('portal.service_requests_page.create.type_consultation') }}</option>
                <option value="research">{{ __('portal.service_requests_page.create.type_research') }}</option>
                <option value="copyright">{{ __('portal.service_requests_page.create.type_copyright') }}</option>
            </select>
        </div>
    </div>
    <div class="card-content">
        @if($requests->count() > 0)
            <div class="review-queue">
                @foreach($requests as $request)
                <div class="review-item d-flex align-center justify-between mb-3 p-4 rounded-lg" 
                     style="background: var(--bg-tertiary); border-left: 4px solid {{ $request->priority === 'urgent' ? 'var(--error-color)' : ($request->priority === 'high' ? 'var(--warning-color)' : 'var(--primary-color)') }};">
                    <div class="d-flex align-center">
                        <div class="request-icon {{ $request->type === 'idea' ? 'primary' : ($request->type === 'consultation' ? 'success' : ($request->type === 'research' ? 'info' : 'warning')) }}">
                            @if($request->type === 'idea')
                                <i class="fas fa-lightbulb"></i>
                            @elseif($request->type === 'consultation')
                                <i class="fas fa-comments"></i>
                            @elseif($request->type === 'research')
                                <i class="fas fa-search"></i>
                            @else
                                <i class="fas fa-copyright"></i>
                            @endif
                        </div>
                        <div class="request-info">
                            <h4 class="mb-1">{{ $request->title }}</h4>
                            <p class="text-muted mb-1">
                                <strong>{{ __('portal.service_requests_page.review_queue.client_label') }}</strong> {{ $request->user->name }} • 
                                <strong>{{ __('portal.service_requests_page.review_queue.type_label') }}</strong> {{ $request->getTypeDisplayName() }} • 
                                <strong>{{ __('portal.service_requests_page.review_queue.submitted_label') }}</strong> {{ $request->created_at->diffForHumans() }}
                            </p>
                            <p class="request-description">{{ Str::limit($request->description, 150) }}</p>
                        </div>
                    </div>
                    <div class="request-actions d-flex align-center gap-2">
                        <span class="priority-badge {{ $request->getPriorityBadgeColor() }}">
                            {{ __('portal.service_requests_page.priority.' . $request->priority) }}
                        </span>
                        <a href="{{ route('service-requests.show', $request) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i> {{ __('portal.service_requests_page.review_queue.review') }}
                        </a>
                        <button class="btn btn-success btn-sm" onclick="quickApprove({{ $request->id }})">
                            <i class="fas fa-check"></i> {{ __('portal.service_requests_page.review_queue.approve') }}
                        </button>
                        <button class="btn btn-error btn-sm" onclick="quickReject({{ $request->id }})">
                            <i class="fas fa-times"></i> {{ __('portal.service_requests_page.review_queue.reject') }}
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $requests->links('pagination::bootstrap-5') }}
            </div>
        @else
            <div class="text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4>{{ __('portal.service_requests_page.review_queue.empty_title') }}</h4>
                    <p class="text-muted">{{ __('portal.service_requests_page.review_queue.empty_body') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Quick Review Modal -->
<div class="modal fade" id="quickReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('portal.service_requests_page.review_queue.modal_title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="quickReviewForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" id="quickAction">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.service_requests_page.review_queue.modal_notes_label') }}</label>
                        <textarea name="review_notes" rows="3" class="form-control" 
                                  placeholder="{{ __('portal.service_requests_page.review_queue.modal_notes_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.service_requests_page.cancel') }}</button>
                    <button type="submit" class="btn" id="quickSubmitBtn">{{ __('portal.service_requests_page.review_queue.approve') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.request-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: var(--space-md);
    color: white;
    font-size: var(--font-size-lg);
}

.request-icon.primary { background: var(--primary-color); }
.request-icon.success { background: var(--success-color); }
.request-icon.info { background: var(--info-color); }
.request-icon.warning { background: var(--warning-color); }

.priority-badge {
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.priority-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.priority-badge.warning {
    background: #fef3c7;
    color: #92400e;
}

.priority-badge.info {
    background: #dbeafe;
    color: #1e40af;
}

.priority-badge.error {
    background: #fee2e2;
    color: #991b1b;
}

.text-muted {
    color: var(--gray-500);
}

.gap-2 {
    gap: var(--space-sm);
}

.empty-state {
    padding: var(--space-2xl);
}

.empty-state i {
    opacity: 0.5;
}

.request-description {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    line-height: 1.4;
}
</style>
@endpush

@push('scripts')
<script>
const reviewQueueLabels = {
    approve: @json(__('portal.service_requests_page.review_queue.approve')),
    reject: @json(__('portal.service_requests_page.review_queue.reject')),
};

function quickApprove(requestId) {
    document.getElementById('quickAction').value = 'approve';
    document.getElementById('quickReviewForm').action = `/service-requests/${requestId}/review`;
    document.getElementById('quickSubmitBtn').textContent = reviewQueueLabels.approve;
    document.getElementById('quickSubmitBtn').className = 'btn btn-success';
    new bootstrap.Modal(document.getElementById('quickReviewModal')).show();
}

function quickReject(requestId) {
    document.getElementById('quickAction').value = 'reject';
    document.getElementById('quickReviewForm').action = `/service-requests/${requestId}/review`;
    document.getElementById('quickSubmitBtn').textContent = reviewQueueLabels.reject;
    document.getElementById('quickSubmitBtn').className = 'btn btn-error';
    new bootstrap.Modal(document.getElementById('quickReviewModal')).show();
}

function filterByPriority(priority) {
    const url = new URL(window.location);
    if (priority) {
        url.searchParams.set('priority', priority);
    } else {
        url.searchParams.delete('priority');
    }
    window.location.href = url.toString();
}

function filterByType(type) {
    const url = new URL(window.location);
    if (type) {
        url.searchParams.set('type', type);
    } else {
        url.searchParams.delete('type');
    }
    window.location.href = url.toString();
}
</script>
@endpush
