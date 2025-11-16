@extends('layouts.dashboard')

@section('title', 'Client Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Active Projects Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Active Projects</h3>
            <div class="widget-icon primary">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Track your ongoing projects and their progress</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['active_projects'] }}</span>
                    <span class="stat-label">Active</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['pending_projects'] }}</span>
                    <span class="stat-label">Pending</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['completed_projects'] }}</span>
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
                <i class="fas fa-lightbulb"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Submit new service requests or track existing ones</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['requests_in_review'] }}</span>
                    <span class="stat-label">In Review</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['requests_approved'] }}</span>
                    <span class="stat-label">Approved</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Meetings Widget -->
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Upcoming Meetings</h3>
            <div class="widget-icon warning">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Your scheduled meetings and consultations</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['meetings_this_week'] }}</span>
                    <span class="stat-label">This Week</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['meetings_today'] }}</span>
                    <span class="stat-label">Today</span>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Tools Widget -->
    {{-- <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">AI Assessment Tools</h3>
            <div class="widget-icon info">
                <i class="fas fa-robot"></i>
            </div>
        </div>
        <div class="widget-content">
            <p>Enhance your ideas with AI-powered tools</p>
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['total_tokens'] > 0 ? $stats['total_tokens'] : 'N/A' }}</span>
                    <span class="stat-label">Tokens Used</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $stats['ai_assessments'] }}</span>
                    <span class="stat-label">Assessments</span>
                </div>
            </div>
        </div>
    </div> --}}
</div>

<!-- Recent Activity Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recent Activity</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">View All Services</a>
    </div>
    <div class="card-content">
        <div class="activity-list">
            @forelse($recentActivities as $activity)
            <div class="activity-item d-flex align-center mb-3">
                <div class="activity-icon {{ $activity['color'] }}">
                    <i class="fas fa-{{ $activity['icon'] }}"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-1"><strong>{{ $activity['title'] }}</strong> - {{ $activity['status'] }}</p>
                    <small class="text-muted">{{ $activity['time']->diffForHumans() }}</small>
                </div>
            </div>
            @empty
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                <p class="text-muted">No recent activity. Start by requesting a service!</p>
                <a href="{{ route('services.index') }}" class="btn btn-primary btn-sm mt-2">
                    <i class="fas fa-plus"></i> Request Service
                </a>
            </div>
            @endforelse
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
            <a href="{{ route('services.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Request Service
            </a>
            <a href="{{ route('client.requests') }}" class="btn btn-secondary">
                <i class="fas fa-list"></i>
                My Requests
            </a>
            <a href="{{ route('ideas.create') }}" class="btn btn-secondary">
                <i class="fas fa-lightbulb"></i>
                Idea Generation
            </a>
            <a href="{{ route('consultations.create') }}" class="btn btn-secondary">
                <i class="fas fa-comments"></i>
                Consultation
            </a>
        </div>
    </div>
</div>

<!-- Project Status Overview -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Active Projects Overview</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="card-content">
        <div class="project-list">
            @forelse($activeProjects as $project)
            <div class="project-item d-flex align-center justify-between mb-3 p-3 rounded-lg" style="background: var(--bg-tertiary);">
                <div class="d-flex align-center">
                    <div class="project-icon {{ $project['color'] }}">
                        <i class="fas fa-{{ $project['icon'] }}"></i>
                    </div>
                    <div class="project-info">
                        <h4 class="mb-1">{{ $project['title'] }}</h4>
                        <p class="text-muted mb-0">{{ $project['description'] }}</p>
                    </div>
                </div>
                <div class="project-status">
                    <span class="status-badge {{ strtolower($project['status']) === 'approved' ? 'success' : 'primary' }}">
                        {{ $project['status'] }}
                    </span>
                    <div class="progress-bar mt-2">
                        <div class="progress-fill" style="width: {{ $project['progress'] }}%;"></div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-4">
                <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                <p class="text-muted">No active projects yet. Submit a service request to get started!</p>
                <a href="{{ route('services.index') }}" class="btn btn-primary btn-sm mt-2">
                    <i class="fas fa-plus"></i> Request Service
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>

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

.project-icon {
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

.project-icon.primary { background: var(--primary-color); }
.project-icon.success { background: var(--success-color); }
.project-icon.info { background: var(--info-color); }

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
</style>
@endpush
