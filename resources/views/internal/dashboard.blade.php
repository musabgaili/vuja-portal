@extends('layouts.internal-dashboard')

@section('title', __('portal.internal.page_title'))
@section('page-title', __('portal.internal.page_heading'))

@section('content')
<!-- Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Task Overview Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.internal.my_tasks') }}</h3>
            <div class="widget-icon primary">
                <i class="fas fa-tasks"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>{{ __('portal.internal.track_tasks_assigned') }}</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">8</span>
                    <span class="stat-label">{{ __('portal.internal.label_assigned') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">5</span>
                    <span class="stat-label">{{ __('portal.client.requests.in_progress') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">12</span>
                    <span class="stat-label">{{ __('portal.stat_completed') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Requests Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.service_requests') }}</h3>
            <div class="widget-icon success">
                <i class="fas fa-inbox"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>{{ __('portal.internal.manage_incoming_requests') }}</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">3</span>
                    <span class="stat-label">{{ __('portal.internal.label_new') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">7</span>
                    <span class="stat-label">{{ __('portal.stat_in_review') }}</span>
                </div>
                @if(auth()->user()->isManager())
                <div class="stat-item">
                    <span class="stat-number">2</span>
                    <span class="stat-label">{{ __('portal.internal.pending_approval') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Project Status Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.internal.project_status') }}</h3>
            <div class="widget-icon warning">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>{{ __('portal.internal.overview_project_involvement') }}</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">4</span>
                    <span class="stat-label">{{ __('portal.stat_active') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">2</span>
                    <span class="stat-label">{{ __('portal.internal.label_planning') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">6</span>
                    <span class="stat-label">{{ __('portal.stat_completed') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance Widget -->
    @if(auth()->user()->isManager() )
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.internal.team_performance') }}</h3>
            <div class="widget-icon info">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>{{ __('portal.internal.monitor_team_metrics') }}</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">8</span>
                    <span class="stat-label">{{ __('portal.internal.label_team_members') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">95%</span>
                    <span class="stat-label">{{ __('portal.internal.on_time') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">4.8</span>
                    <span class="stat-label">{{ __('portal.internal.rating') }}</span>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Personal Performance Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.internal.my_performance') }}</h3>
            <div class="widget-icon info">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>{{ __('portal.internal.track_productivity') }}</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">98%</span>
                    <span class="stat-label">{{ __('portal.internal.on_time') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">4.9</span>
                    <span class="stat-label">{{ __('portal.internal.rating') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">25</span>
                    <span class="stat-label">{{ __('portal.internal.tasks_done') }}</span>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Recent Activity Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.internal.recent_activity') }}</h3>
        <a href="#" class="btn btn-secondary btn-sm">{{ __('portal.internal.view_all') }}</a>
    </div>
    <div class="card-content">
        <div class="activity-list">
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon success">
                    <i class="fas fa-check"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1">{{ __('portal.internal.activity_line_1') }}</p>
                    <small class="text-muted">{{ __('portal.internal.time_1_hour_ago') }}</small>
                </div>
            </div>
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon info">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1">{{ __('portal.internal.activity_line_2') }}</p>
                    <small class="text-muted">{{ __('portal.internal.time_3_hours_ago') }}</small>
                </div>
            </div>
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon warning">
                    <i class="fas fa-inbox"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1">{{ __('portal.internal.activity_line_3') }}</p>
                    <small class="text-muted">{{ __('portal.internal.time_5_hours_ago') }}</small>
                </div>
            </div>
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon primary">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1">{{ __('portal.internal.activity_line_4') }}</p>
                    <small class="text-muted">{{ __('portal.internal.time_1_day_ago') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.internal.quick_actions') }}</h3>
    </div>
    <div class="card-content">
        <div class="d-flex gap-2 flex-wrap">
            <a href="#" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                {{ __('portal.internal.create_task') }}
            </a>
            <a href="#" class="btn btn-secondary">
                <i class="fas fa-calendar-plus"></i>
                {{ __('portal.internal.schedule_meeting') }}
            </a>
            <a href="#" class="btn btn-secondary">
                <i class="fas fa-comment"></i>
                {{ __('portal.internal.add_comment') }}
            </a>
            @if(auth()->user()->isManager())
            <a href="{{ route('service-requests.review-queue') }}" class="btn btn-secondary">
                <i class="fas fa-check-circle"></i>
                {{ __('portal.internal.review_requests') }}
            </a>
            @endif
        </div>
    </div>
</div>

<!-- Task List -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.internal.recent_tasks') }}</h3>
        <a href="#" class="btn btn-secondary btn-sm">{{ __('portal.internal.view_all_tasks') }}</a>
    </div>
    <div class="card-content">
        <div class="task-list">
            <div class="task-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="task-icon primary">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="task-info">
                        <h4 class="mb-1">{{ __('portal.internal.demo_task_frontend') }}</h4>
                        <p class="text-muted mb-0">{{ __('portal.internal.demo_proj_mobile') }}</p>
                    </div>
                </div>
                <div class="task-status">
                    <span class="status-badge success">{{ __('portal.client.requests.in_progress') }}</span>
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
                        <h4 class="mb-1">{{ __('portal.internal.demo_task_ui_design') }}</h4>
                        <p class="text-muted mb-0">{{ __('portal.internal.demo_proj_website') }}</p>
                    </div>
                </div>
                <div class="task-status">
                    <span class="status-badge success">{{ __('portal.stat_completed') }}</span>
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
                        <h4 class="mb-1">{{ __('portal.internal.demo_task_research') }}</h4>
                        <p class="text-muted mb-0">{{ __('portal.internal.demo_proj_market') }}</p>
                    </div>
                </div>
                <div class="task-status">
                    <span class="status-badge warning">{{ __('portal.stat_pending') }}</span>
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
        <h3 class="card-title">{{ __('portal.internal.service_request_queue') }}</h3>
        <a href="#" class="btn btn-secondary btn-sm">{{ __('portal.internal.view_all_requests') }}</a>
    </div>
    <div class="card-content">
        <div class="request-list">
            <div class="request-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="request-icon primary">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="request-info">
                        <h4 class="mb-1">{{ __('portal.internal.demo_idea_gen_title') }}</h4>
                        <p class="text-muted mb-0">{{ __('portal.internal.demo_meta_smith') }}</p>
                    </div>
                </div>
                <div class="request-actions">
                    <a href="#" class="btn btn-success btn-sm">{{ __('portal.internal.approve') }}</a>
                    <a href="#" class="btn btn-error btn-sm">{{ __('portal.internal.reject') }}</a>
                </div>
            </div>
            
            <div class="request-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="request-icon info">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="request-info">
                        <h4 class="mb-1">{{ __('portal.internal.demo_consultation_title') }}</h4>
                        <p class="text-muted mb-0">{{ __('portal.internal.demo_meta_johnson') }}</p>
                    </div>
                </div>
                <div class="request-actions">
                    <a href="#" class="btn btn-success btn-sm">{{ __('portal.internal.approve') }}</a>
                    <a href="#" class="btn btn-error btn-sm">{{ __('portal.internal.reject') }}</a>
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
