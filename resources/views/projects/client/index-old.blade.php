@extends('layouts.dashboard')
@section('title', 'My Projects')
@section('content')
<div class="card mb-4">
    <div class="card-header">
        <h3>My Projects</h3>
    </div>
    <div class="card-content">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Projects</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['active'] }}</div>
                    <div class="stat-label">Active</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-value">{{ $stats['completed'] }}</div>
                    <div class="stat-label">Completed</div>
                </div>
            </div>
        </div>

        @forelse($projects as $project)
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5>{{ $project->title }}</h5>
                        <small class="text-muted">Started: {{ $project->created_at->format('M d, Y') }}</small>
                    </div>
                    <span class="status-badge {{ $project->getStatusBadgeColor() }}">{{ $project->getStatusLabel() }}</span>
                </div>
            </div>
            <div class="card-content">
                <p>{{ Str::limit($project->description, 150) }}</p>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Progress:</strong>
                        <div class="progress-bar mt-1">
                            <div class="progress-fill" style="width:{{ $project->completion_percentage }}%;"></div>
                        </div>
                        <small>{{ $project->completion_percentage }}%</small>
                    </div>
                    <div class="col-md-6">
                        <strong>Milestones:</strong> {{ $project->milestones->count() }} total, 
                        {{ $project->milestones->where('status', 'completed')->count() }} completed
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-info">{{ $project->tasks->count() }} Tasks</span>
                        <span class="badge bg-secondary">{{ $project->getTeamMembers()->count() }} Team Members</span>
                    </div>
                    <a href="{{ route('projects.client.show', $project) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <p class="text-muted text-center py-4">No projects yet.</p>
        @endforelse

        {{ $projects->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
