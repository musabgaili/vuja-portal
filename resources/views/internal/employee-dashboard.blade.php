@extends('layouts.internal-dashboard')
@section('title', 'My Dashboard')
@section('content')

<style>
.modern-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
}
.stat-card-modern {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border-left: 4px solid;
}
.stat-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.stat-number-modern {
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.stat-label-modern {
    color: #64748b;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}
.card-modern {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.card-modern-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 2px solid #f1f5f9;
    display: flex;
    justify-content: between;
    align-items: center;
}
.card-modern-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
}
.card-modern-content {
    padding: 1.5rem;
}
.item-modern {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    border-left: 3px solid #667eea;
    transition: all 0.2s;
}
.item-modern:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}
.badge-modern {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

<div class="modern-hero">
    <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">Welcome back, {{ auth()->user()->name }}! 👋</h1>
    <p style="margin: 0.5rem 0 0 0; opacity: 0.95; font-size: 1.1rem;">Here's what's on your plate today</p>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card-modern" style="border-color: #3b82f6;">
            <div class="stat-number-modern">{{ $stats['total_assigned'] }}</div>
            <div class="stat-label-modern">Assigned to Me</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-modern" style="border-color: #f59e0b;">
            <div class="stat-number-modern">{{ $stats['in_progress'] }}</div>
            <div class="stat-label-modern">In Progress</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-modern" style="border-color: #10b981;">
            <div class="stat-number-modern">{{ $stats['meetings_today'] }}</div>
            <div class="stat-label-modern">Meetings Today</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-modern" style="border-color: #8b5cf6;">
            <div class="stat-number-modern">{{ $stats['assigned_projects'] ?? 0 }}</div>
            <div class="stat-label-modern">Active Projects</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-modern-header">
                <h3><i class="fas fa-lightbulb text-warning"></i> My Assigned Ideas</h3>
                <a href="{{ route('ideas.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-modern-content">
                @forelse($assignedIdeas as $idea)
                <div class="item-modern">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong style="color: #1e293b;">{{ $idea->title }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $idea->user->name }}</small>
                        </div>
                        <span class="badge-modern bg-{{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No ideas assigned to you.</p>
                @endforelse
            </div>
        </div>

        <div class="card-modern">
            <div class="card-modern-header">
                <h3><i class="fas fa-comments text-info"></i> My Consultations</h3>
                <a href="{{ route('consultations.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-modern-content">
                @forelse($assignedConsultations as $consultation)
                <div class="item-modern">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong style="color: #1e293b;">{{ $consultation->title }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $consultation->user->name }}</small>
                        </div>
                        <span class="badge-modern bg-{{ $consultation->getStatusBadgeColor() }}">{{ $consultation->getStatusLabel() }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No consultations assigned to you.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-modern">
            <div class="card-modern-header">
                <h3><i class="fas fa-search text-success"></i> My Research Projects</h3>
                <a href="{{ route('research.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-modern-content">
                @forelse($assignedResearch as $research)
                <div class="item-modern">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong style="color: #1e293b;">{{ $research->title }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $research->user->name }}</small>
                        </div>
                        <span class="badge-modern bg-{{ $research->getStatusBadgeColor() }}">{{ $research->getStatusLabel() }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No research projects assigned to you.</p>
                @endforelse
            </div>
        </div>

        <div class="card-modern">
            <div class="card-modern-header">
                <h3><i class="fas fa-calendar-alt text-primary"></i> My Meetings This Week</h3>
                <a href="{{ route('meetings.internal.my-meetings') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-modern-content">
                @forelse($upcomingMeetings as $meeting)
                <div class="item-modern">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong style="color: #1e293b;">{{ $meeting->title }}</strong>
                            <br><small class="text-muted"><i class="fas fa-clock"></i> {{ $meeting->scheduled_at->format('M d, g:i A') }}</small>
                            <br><small class="text-info"><i class="fas fa-user"></i> {{ $meeting->client->name }}</small>
                        </div>
                        <span class="badge-modern bg-{{ $meeting->getStatusBadgeColor() }}">{{ ucfirst($meeting->status) }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-calendar-times"></i> No meetings this week.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
