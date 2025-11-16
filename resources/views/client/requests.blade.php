@extends('layouts.dashboard')

@section('title', 'My Requests')
@section('page-title', 'My Service Requests')

@section('content')
<!-- Summary Stats -->
<div class="dashboard-grid mb-4">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Total Requests</h3>
            <div class="widget-icon primary">
                <i class="fas fa-list"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['total'] }}</span>
                    <span class="stat-label">All Time</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Pending</h3>
            <div class="widget-icon warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['pending'] }}</span>
                    <span class="stat-label">Awaiting Review</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">In Progress</h3>
            <div class="widget-icon info">
                <i class="fas fa-spinner"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['in_progress'] }}</span>
                    <span class="stat-label">Active</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Completed</h3>
            <div class="widget-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['completed'] }}</span>
                    <span class="stat-label">Finished</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Type Breakdown -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Requests by Service Type</h3>
    </div>
    <div class="card-content">
        <div class="service-breakdown">
            <div class="service-stat" style="border-left-color: #f59e0b;">
                <i class="fas fa-lightbulb" style="color: #f59e0b;"></i>
                <div>
                    <strong>{{ $summary['ideas'] }}</strong>
                    <span>Ideas</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #10b981;">
                <i class="fas fa-comments" style="color: #10b981;"></i>
                <div>
                    <strong>{{ $summary['consultations'] }}</strong>
                    <span>Consultations</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #3b82f6;">
                <i class="fas fa-search" style="color: #3b82f6;"></i>
                <div>
                    <strong>{{ $summary['research'] }}</strong>
                    <span>Research</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #8b5cf6;">
                <i class="fas fa-file-contract" style="color: #8b5cf6;"></i>
                <div>
                    <strong>{{ $summary['ip'] }}</strong>
                    <span>IP Registration</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #ec4899;">
                <i class="fas fa-copyright" style="color: #ec4899;"></i>
                <div>
                    <strong>{{ $summary['copyright'] }}</strong>
                    <span>Copyright</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Requests Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All My Requests</h3>
        <div class="d-flex gap-2">
            <select class="form-control" style="width: auto;" onchange="filterByType(this.value)">
                <option value="">All Services</option>
                <option value="idea" {{ $typeFilter === 'idea' ? 'selected' : '' }}>Ideas</option>
                <option value="consultation" {{ $typeFilter === 'consultation' ? 'selected' : '' }}>Consultations</option>
                <option value="research" {{ $typeFilter === 'research' ? 'selected' : '' }}>Research</option>
                <option value="ip" {{ $typeFilter === 'ip' ? 'selected' : '' }}>IP Registration</option>
                <option value="copyright" {{ $typeFilter === 'copyright' ? 'selected' : '' }}>Copyright</option>
            </select>
            <select class="form-control" style="width: auto;" onchange="filterByStatus(this.value)">
                <option value="">All Status</option>
                <option value="submitted">Submitted</option>
                <option value="negotiation">Negotiation</option>
                <option value="approved">Approved</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>
    <div class="card-content">
        @if($allRequests->count() > 0)
            <div class="requests-list">
                @foreach($allRequests as $req)
                <div class="request-card mb-3" style="border-left: 4px solid {{ $req['type_color'] }};">
                    <div class="request-header">
                        <div class="d-flex align-center">
                            <div class="request-type-icon" style="background: {{ $req['type_color'] }};">
                                <i class="fas fa-{{ $req['type_icon'] }}"></i>
                            </div>
                            <div class="request-info">
                                <h4 class="mb-1">{{ $req['title'] }}</h4>
                                <p class="text-muted mb-1">
                                    <span class="badge" style="background: {{ $req['type_color'] }}; color: white;">
                                        {{ $req['type_label'] }}
                                    </span>
                                    • Submitted {{ $req['created_at']->format('M d, Y') }}
                                    @if($req['assigned_to'])
                                        • Assigned to {{ $req['assigned_to'] }}
                                    @endif
                                </p>
                                <p class="request-description">{{ Str::limit($req['description'], 120) }}</p>
                            </div>
                        </div>
                        <div class="request-meta">
                            <span class="status-badge {{ $req['status_color'] }}">
                                {{ $req['status_label'] }}
                            </span>
                            @if($req['has_quote'])
                            <div class="quote-badge">
                                <i class="fas fa-dollar-sign"></i>
                                ${{ number_format($req['quote_amount'], 2) }}
                            </div>
                            @endif
                            @if(isset($req['meeting_date']) && $req['meeting_date'])
                            <div class="meeting-badge">
                                <i class="fas fa-calendar"></i>
                                {{ $req['meeting_date']->format('M d') }}
                            </div>
                            @endif
                            @if(isset($req['registration_number']) && $req['registration_number'])
                            <div class="reg-badge">
                                <i class="fas fa-certificate"></i>
                                {{ $req['registration_number'] }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="request-footer">
                        <span class="text-muted">Last updated {{ $req['updated_at']->diffForHumans() }}</span>
                        <a href="{{ $req['view_url'] }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No Service Requests Yet</h4>
                <p class="text-muted">You haven't submitted any service requests yet. Start by requesting a service!</p>
                <a href="{{ route('services.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus"></i> Request New Service
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.service-breakdown {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: var(--space-md);
}

.service-stat {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    border-left: 3px solid;
}

.service-stat i {
    font-size: var(--font-size-xl);
}

.service-stat strong {
    display: block;
    font-size: var(--font-size-lg);
    color: var(--text-color);
}

.service-stat span {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.request-card {
    background: var(--card-bg);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    box-shadow: var(--shadow-light);
    transition: all 0.3s ease;
}

.request-card:hover {
    box-shadow: var(--shadow-medium);
    transform: translateY(-2px);
}

.request-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-md);
}

.request-type-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: var(--font-size-lg);
    margin-right: var(--space-md);
    flex-shrink: 0;
}

.request-info h4 {
    font-size: var(--font-size-lg);
    color: var(--text-color);
    font-weight: 600;
}

.request-description {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    margin: 0;
}

.request-meta {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
    align-items: flex-end;
}

.quote-badge, .meeting-badge, .reg-badge {
    padding: var(--space-xs) var(--space-sm);
    background: var(--primary-color);
    color: white;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: 600;
}

.meeting-badge {
    background: var(--success-color);
}

.reg-badge {
    background: var(--info-color);
}

.request-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--space-md);
    border-top: 1px solid var(--gray-200);
}

.status-badge {
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge.success { background: #d1fae5; color: #065f46; }
.status-badge.warning { background: #fef3c7; color: #92400e; }
.status-badge.info { background: #dbeafe; color: #1e40af; }
.status-badge.error { background: #fee2e2; color: #991b1b; }
.status-badge.primary { background: #dbeafe; color: #1e40af; }
.status-badge.secondary { background: #f1f5f9; color: #475569; }

.text-muted {
    color: var(--gray-500);
}

.gap-2 {
    gap: var(--space-sm);
}
</style>
@endpush

@push('scripts')
<script>
function filterByType(type) {
    const url = new URL(window.location);
    if (type) {
        url.searchParams.set('type', type);
    } else {
        url.searchParams.delete('type');
    }
    window.location.href = url.toString();
}

function filterByStatus(status) {
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
{{-- @endsection --}}

