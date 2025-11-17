@extends('layouts.dashboard')
@section('title', 'My Projects')
@section('content')

<style>
.projects-hero {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 20px 60px rgba(79, 172, 254, 0.3);
}
.projects-hero h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}
.stat-card-modern {
    background: white;
    padding: 2rem;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s;
}
.stat-card-modern:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
}
.stat-card-value {
    font-size: 3rem;
    font-weight: 700;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.stat-card-label {
    color: #64748b;
    font-size: 1rem;
    margin-top: 0.5rem;
    font-weight: 500;
}
.project-card-modern {
    background: white;
    border-radius: 16px;
    padding: 0;
    margin-bottom: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: all 0.3s;
    border: 2px solid transparent;
}
.project-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    border-color: #4facfe;
}
.project-card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    padding: 1.5rem;
    border-bottom: 2px solid #e2e8f0;
}
.project-card-body {
    padding: 1.5rem;
}
.project-card-footer {
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.progress-bar-client {
    height: 10px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}
.progress-bar-client-fill {
    height: 100%;
    background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
    box-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
}
</style>

<!-- Hero Section -->
<div class="projects-hero">
    <div class="text-center">
        <h1><i class="fas fa-folder-open"></i> My Projects</h1>
        <p style="opacity: 0.9; font-size: 1.1rem; margin: 0;">Track your projects, view progress, and collaborate with the team</p>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card-modern">
            <div class="stat-card-value">{{ $stats['total'] }}</div>
            <div class="stat-card-label">Total Projects</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-modern">
            <div class="stat-card-value" style="background: linear-gradient(135deg, #10b981 0%, #1C575F 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                {{ $stats['active'] }}
            </div>
            <div class="stat-card-label">Active</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-modern">
            <div class="stat-card-value" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                {{ $stats['completed'] }}
            </div>
            <div class="stat-card-label">Completed</div>
        </div>
    </div>
</div>

<!-- Projects List -->
@forelse($projects as $project)
<div class="project-card-modern">
    <div class="project-card-header">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex: 1;">
                <h4 style="font-weight: 600; color: #1e293b; margin-bottom: 0.5rem;">{{ $project->title }}</h4>
                <small style="color: #64748b;">
                    <i class="fas fa-calendar"></i> Started {{ $project->created_at->format('M d, Y') }}
                </small>
            </div>
            <span class="status-badge {{ $project->getStatusBadgeColor() }}" style="font-size: 0.9rem;">
                {{ $project->getStatusLabel() }}
            </span>
        </div>
    </div>
    
    <div class="project-card-body">
        <p style="color: #64748b; line-height: 1.6; margin-bottom: 1.5rem;">
            {{ Str::limit($project->description, 180) }}
        </p>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <div style="margin-bottom: 0.5rem;">
                    <strong style="color: #1e293b;">Progress</strong>
                </div>
                <div class="progress-bar-client">
                    <div class="progress-bar-client-fill" style="width:{{ $project->completion_percentage }}%;"></div>
                </div>
                <small style="color: #64748b;">{{ $project->completion_percentage }}% Complete</small>
            </div>
            <div class="col-md-6">
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-info" style="font-size: 0.875rem; padding: 0.5rem 0.75rem;">
                        <i class="fas fa-flag"></i> {{ $project->milestones->count() }} Milestones
                    </span>
                    <span class="badge bg-secondary" style="font-size: 0.875rem; padding: 0.5rem 0.75rem;">
                        <i class="fas fa-tasks"></i> {{ $project->tasks->count() }} Tasks
                    </span>
                    <span class="badge bg-success" style="font-size: 0.875rem; padding: 0.5rem 0.75rem;">
                        <i class="fas fa-check"></i> {{ $project->tasks->where('status', 'completed')->count() }} Done
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="project-card-footer">
        <div>
            <small style="color: #64748b;">
                <i class="fas fa-users"></i> {{ $project->getTeamMembers()->count() }} team members
            </small>
        </div>
        <a href="{{ route('projects.client.show', $project) }}" class="btn btn-primary" style="border-radius: 10px; padding: 0.5rem 1.5rem;">
            <i class="fas fa-arrow-right"></i> View Project
        </a>
    </div>
</div>
@empty
<div class="text-center py-5">
    <i class="fas fa-folder-open" style="font-size: 5rem; color: #cbd5e1; margin-bottom: 1.5rem;"></i>
    <h4 style="color: #64748b;">No Projects Yet</h4>
    <p style="color: #94a3b8;">Your projects will appear here once they're created.</p>
</div>
@endforelse

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $projects->links('pagination::bootstrap-5') }}
</div>

@endsection

