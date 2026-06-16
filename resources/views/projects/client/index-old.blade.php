@extends('layouts.dashboard')
@section('title', __('portal.projects_client.index.title'))
@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h3>{{ __('portal.projects_client.index.my_projects') }}</h3>
    </div>
    <div class="card-content">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">{{ __('portal.projects_client.index.total_projects') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-label">{{ __('portal.projects_client.index.active') }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['completed'] }}</div>
                    <div class="stat-label">{{ __('portal.projects_client.index.completed') }}</div>
                </div>
            </div>
        </div>

        @forelse($projects as $project)
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5>{{ $project->title }}</h5>
                        <small class="text-muted">{{ __('portal.projects_client.index.started') }}: {{ $project->created_at->translatedFormat('M d, Y') }}</small>
                    </div>
                    <span class="status-badge {{ $project->getStatusBadgeColor() }}">{{ $project->getStatusLabel() }}</span>
                </div>
            </div>
            <div class="card-content">
                <p>{{ Str::limit($project->description, 150) }}</p>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>{{ __('portal.projects_client.index.progress') }}:</strong>
                        <div class="progress-bar mt-1">
                            <div class="progress-fill" style="width:{{ $project->completion_percentage }}%;"></div>
                        </div>
                        <small>{{ $project->completion_percentage }}%</small>
                    </div>
                    <div class="col-md-6">
                        <strong>{{ __('portal.projects_client.index.milestones') }}:</strong> {{ $project->milestones->count() }} {{ __('portal.projects_client.index.total') }},
                        {{ $project->milestones->where('status', 'completed')->count() }} {{ __('portal.projects_client.index.completed') }}
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-info">{{ $project->tasks->count() }} {{ __('portal.projects_client.index.tasks') }}</span>
                        <span class="badge bg-secondary">{{ $project->getTeamMembers()->count() }} {{ __('portal.projects_client.index.team_members_label') }}</span>
                    </div>
                    <a href="{{ route('projects.client.show', $project) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> {{ __('portal.projects_client.index.view_details') }}
                    </a>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted text-center py-4">{{ __('portal.projects_client.index.empty_old') }}</p>
        @endforelse

        {{ $projects->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
