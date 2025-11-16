@extends('layouts.internal-dashboard')

@section('title', 'Internal Dashboard')
@section('page-title', 'Internal Dashboard')

@section('content')
<!-- Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Task Overview Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">My Tasks</h3>
            <div class="widget-icon primary">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Track your assigned tasks and progress</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">8</span>
                    <span class="stat-label">Assigned</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">5</span>
                    <span class="stat-label">In Progress</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">12</span>
                    <span class="stat-label">Completed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Requests Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Service Requests</h3>
            <div class="widget-icon success">
                <i class="fas fa-inbox"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Manage incoming service requests</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">3</span>
                    <span class="stat-label">New</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">7</span>
                    <span class="stat-label">In Review</span>
                </div>
                @if(auth()->user()->isManager())
                <div class="stat-item">
                    <span class="stat-number">2</span>
                    <span class="stat-label">Pending Approval</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Project Status Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Project Status</h3>
            <div class="widget-icon warning">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Overview of your project involvement</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">4</span>
                    <span class="stat-label">Active</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">2</span>
                    <span class="stat-label">Planning</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">6</span>
                    <span class="stat-label">Completed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance Widget -->
    @if(auth()->user()->isManager() )
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Team Performance</h3>
            <div class="widget-icon info">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Monitor team productivity and metrics</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">8</span>
                    <span class="stat-label">Team Members</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">95%</span>
                    <span class="stat-label">On Time</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">4.8</span>
                    <span class="stat-label">Rating</span>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Personal Performance Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">My Performance</h3>
            <div class="widget-icon info">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Track your productivity and achievements</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">98%</span>
                    <span class="stat-label">On Time</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">4.9</span>
                    <span class="stat-label">Rating</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">25</span>
                    <span class="stat-label">Tasks Done</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Recent Activity Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Activity</h3>
        <a href="#" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="card-content">
        <div class="activity-list">
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon success">
                    <i class="fas fa-check"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1"><strong>Task "UI Design Review"</strong> completed</p>
                    <small class="text-muted">1 hour ago</small>
                </div>
            </div>
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon info">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1"><strong>New comment</strong> on Project "Mobile App"</p>
                    <small class="text-muted">3 hours ago</small>
                </div>
            </div>
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon warning">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1"><strong>New service request</strong> assigned to you</p>
                    <small class="text-muted">5 hours ago</small>
                </div>
            </div>
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon primary">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1"><strong>Meeting scheduled</strong> for tomorrow at 10:00 AM</p>
                    <small class="text-muted">1 day ago</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-content">
        <div class="d-flex gap-2 flex-wrap">
            <a href="#" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Create Task
            </a>
            <a href="#" class="btn btn-secondary">
                <i class="fas fa-calendar-plus"></i>
                Schedule Meeting
            </a>
            <a href="#" class="btn btn-secondary">
                <i class="fas fa-comment"></i>
                Add Comment
            </a>
            @if(auth()->user()->isManager())
            <a href="{{ route('service-requests.review-queue') }}" class="btn btn-secondary">
                <i class="fas fa-check-circle"></i>
                Review Requests
            </a>
            @endif
        </div>
    </div>
</div>

<!-- Task List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Tasks</h3>
        <a href="#" class="btn btn-secondary btn-sm">View All Tasks</a>
    </div>
    <div class="card-content">
        <div class="task-list">
            <div class="task-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="task-icon primary">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="task-info">
                        <h4 class="mb-1">Frontend Development</h4>
                        <p class="text-muted mb-0">Mobile App Project</p>
                    </div>
                </div>
                <div class="task-status">
                    <span class="status-badge success">In Progress</span>
                    <div class="progress-bar mt-2">
                        <div class="progress-fill" style="width: 60%;"></div>
                    </div>
                </div>
            </div>
            
            <div class="task-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="task-icon success">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="task-info">
                        <h4 class="mb-1">UI Design Review</h4>
                        <p class="text-muted mb-0">Website Project</p>
                    </div>
                </div>
                <div class="task-status">
                    <span class="status-badge success">Completed</span>
                    <div class="progress-bar mt-2">
                        <div class="progress-fill" style="width: 100%;"></div>
                    </div>
                </div>
            </div>
            
            <div class="task-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="task-icon warning">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="task-info">
                        <h4 class="mb-1">Research & Analysis</h4>
                        <p class="text-muted mb-0">Market Research Project</p>
                    </div>
                </div>
                <div class="task-status">
                    <span class="status-badge warning">Pending</span>
                    <div class="progress-bar mt-2">
                        <div class="progress-fill" style="width: 30%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->isManager())
<!-- Service Request Queue -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Service Request Queue</h3>
        <a href="#" class="btn btn-secondary btn-sm">View All Requests</a>
    </div>
    <div class="card-content">
        <div class="request-list">
            <div class="request-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="request-icon primary">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="request-info">
                        <h4 class="mb-1">Idea Generation Request</h4>
                        <p class="text-muted mb-0">Client: John Smith • Submitted 2 hours ago</p>
                    </div>
                </div>
                <div class="request-actions">
                    <a href="#" class="btn btn-success btn-sm">Approve</a>
                    <a href="#" class="btn btn-error btn-sm">Reject</a>
                </div>
            </div>
            
            <div class="request-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="request-icon info">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="request-info">
                        <h4 class="mb-1">Consultation Request</h4>
                        <p class="text-muted mb-0">Client: Sarah Johnson • Submitted 1 day ago</p>
                    </div>
                </div>
                <div class="request-actions">
                    <a href="#" class="btn btn-success btn-sm">Approve</a>
                    <a href="#" class="btn btn-error btn-sm">Reject</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
.activity-item {
    padding: var(--space-md);
    border-radius: var(--radius-lg);
    transition: background var(--transition-fast);
}

.activity-item:hover {
    background: var(--bg-tertiary);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: var(--space-md);
    color: white;
    font-size: var(--font-size-sm);
}

.activity-icon.success { background: var(--success-color); }
.activity-icon.info { background: var(--info-color); }
.activity-icon.warning { background: var(--warning-color); }
.activity-icon.primary { background: var(--primary-color); }

.task-icon, .request-icon {
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

.task-icon.primary, .request-icon.primary { background: var(--primary-color); }
.task-icon.success, .request-icon.success { background: var(--success-color); }
.task-icon.warning, .request-icon.warning { background: var(--warning-color); }
.task-icon.info, .request-icon.info { background: var(--info-color); }

.status-badge {
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.status-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.warning {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.primary {
    background: #dbeafe;
    color: #1e40af;
}

.progress-bar {
    width: 100px;
    height: 6px;
    background: var(--gray-200);
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--primary-color);
    border-radius: 3px;
    transition: width var(--transition-normal);
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

.request-actions {
    display: flex;
    gap: var(--space-sm);
}
</style>
@endpush
