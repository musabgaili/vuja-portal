@extends('layouts.dashboard')

@section('title', 'Service Requests')
@section('page-title', 'My Service Requests')

@section('content')
<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-content">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('service-requests.create', ['type' => 'idea']) }}" class="btn btn-primary">
                <i class="fas fa-lightbulb"></i> Idea Generation
            </a>
            <a href="{{ route('service-requests.create', ['type' => 'consultation']) }}" class="btn btn-success">
                <i class="fas fa-comments"></i> Consultation
            </a>
            <a href="{{ route('service-requests.create', ['type' => 'research']) }}" class="btn btn-info">
                <i class="fas fa-search"></i> Research & IP
            </a>
            <a href="{{ route('service-requests.create', ['type' => 'copyright']) }}" class="btn btn-warning">
                <i class="fas fa-copyright"></i> Copyright Services
            </a>
        </div>
    </div>
</div>

<!-- Service Requests List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Service Requests</h3>
        <div class="d-flex gap-2">
            <select class="form-control" style="width: auto;" onchange="filterRequests(this.value)">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_review">In Review</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>
    <div class="card-content">
        @if($requests->count() > 0)
            <div class="request-list">
                @foreach($requests as $request)
                <div class="request-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
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
                            <p class="text-muted mb-0">
                                {{ $request->getTypeDisplayName() }} • 
                                Submitted {{ $request->created_at->diffForHumans() }}
                                @if($request->assignedTo)
                                    • Assigned to {{ $request->assignedTo->name }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="request-actions d-flex align-center gap-2">
                        <span class="status-badge {{ $request->getStatusBadgeColor() }}">
                            {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                        </span>
                        <span class="priority-badge {{ $request->getPriorityBadgeColor() }}">
                            {{ ucfirst($request->priority) }}
                        </span>
                        <a href="{{ route('service-requests.show', $request) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        @if($request->isPending() && auth()->user()->isClient())
                        <a href="{{ route('service-requests.edit', $request) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endif
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
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4>No Service Requests</h4>
                    <p class="text-muted">You haven't submitted any service requests yet.</p>
                    <a href="{{ route('service-requests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Your First Request
                    </a>
                </div>
            </div>
        @endif
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

.status-badge.secondary, .priority-badge.secondary {
    background: #f1f5f9;
    color: #475569;
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

.empty-state {
    padding: var(--space-2xl);
}

.empty-state i {
    opacity: 0.5;
}
</style>
@endpush

@push('scripts')
<script>
function filterRequests(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}
</script>
@endpush
