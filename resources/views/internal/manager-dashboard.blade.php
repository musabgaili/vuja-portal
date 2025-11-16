@extends('layouts.internal-dashboard')
@section('title', 'Manager Dashboard')
@section('content')

<style>
.manager-hero {
    background: linear-gradient(135deg, #f59e0b 0%, #dc2626 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    box-shadow: 0 8px 32px rgba(245, 158, 11, 0.3);
}
.stat-card-manager {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border-left: 4px solid;
    height: 100%;
}
.stat-card-manager:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.stat-number-manager {
    font-size: 2.5rem;
    font-weight: 700;
}
.stat-label-manager {
    color: #64748b;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
}
.service-card-modern {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 1.5rem;
    transition: all 0.3s;
}
.service-card-modern:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}
.service-header-modern {
    padding: 1.25rem 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.service-header-modern h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1e293b;
}
.service-content-modern {
    padding: 1.5rem;
}
.request-item-modern {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    border-left: 3px solid;
    transition: all 0.2s;
}
.request-item-modern:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}
.quick-stats-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
}
.stat-row-modern {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}
.stat-row-modern:last-child {
    border-bottom: none;
}
</style>

<div class="manager-hero">
    <h1 style="margin: 0; font-size: 2rem; font-weight: 700;">Manager Dashboard 🎯</h1>
    <p style="margin: 0.5rem 0 0 0; opacity: 0.95; font-size: 1.1rem;">Command center for all operations</p>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card-manager" style="border-color: #f59e0b;">
            <div class="stat-number-manager" style="color: #f59e0b;">{{ $stats['new_requests'] }}</div>
            <div class="stat-label-manager">New Requests</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-manager" style="border-color: #10b981;">
            <div class="stat-number-manager" style="color: #10b981;">{{ $stats['active_projects'] }}</div>
            <div class="stat-label-manager">Active Projects</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-manager" style="border-color: #3b82f6;">
            <div class="stat-number-manager" style="color: #3b82f6;">{{ $stats['team_count'] }}</div>
            <div class="stat-label-manager">Team Members</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-manager" style="border-color: #8b5cf6;">
            <div class="stat-number-manager" style="color: #8b5cf6;">{{ $stats['meetings_today'] }}</div>
            <div class="stat-label-manager">Meetings Today</div>
        </div>
    </div>
</div>

<h4 class="mb-3" style="color: #1e293b; font-weight: 600;"><i class="fas fa-bell"></i> New Service Requests</h4>

<div class="row">
    <div class="col-lg-6">
        <div class="service-card-modern">
            <div class="service-header-modern">
                <h3>💡 Idea Generation</h3>
                <a href="{{ route('ideas.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="service-content-modern">
                @forelse($newIdeas as $idea)
                <div class="request-item-modern" style="border-color: #fbbf24;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong style="color: #1e293b;">{{ Str::limit($idea->title, 40) }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $idea->user->name }} • {{ $idea->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span>
                            <br>
                            <a href="{{ route('ideas.manager.show', $idea) }}" class="btn btn-xs btn-secondary mt-1"><i class="fas fa-eye"></i> View</a>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No new ideas.</p>
                @endforelse
            </div>
        </div>

        <div class="service-card-modern">
            <div class="service-header-modern">
                <h3>💬 Consultations</h3>
                <a href="{{ route('consultations.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="service-content-modern">
                @forelse($newConsultations as $consultation)
                <div class="request-item-modern" style="border-color: #3b82f6;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong style="color: #1e293b;">{{ Str::limit($consultation->title, 40) }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $consultation->user->name }} • {{ $consultation->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-info">{{ $consultation->category }}</span>
                            <br>
                            <a href="{{ route('consultations.manager.show', $consultation) }}" class="btn btn-xs btn-secondary mt-1"><i class="fas fa-eye"></i> View</a>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No new consultations.</p>
                @endforelse
            </div>
        </div>

        <div class="service-card-modern">
            <div class="service-header-modern">
                <h3>🔍 Research & IP</h3>
                <a href="{{ route('research.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="service-content-modern">
                @forelse($newResearch as $research)
                <div class="request-item-modern" style="border-color: #10b981;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong style="color: #1e293b;">{{ Str::limit($research->title, 40) }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $research->user->name }} • {{ $research->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $research->getStatusBadgeColor() }}">{{ $research->getStatusLabel() }}</span>
                            <br>
                            <a href="{{ route('research.manager.show', $research) }}" class="btn btn-xs btn-secondary mt-1"><i class="fas fa-eye"></i> View</a>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No new research requests.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="service-card-modern">
            <div class="service-header-modern">
                <h3>📄 IP Registration</h3>
                <a href="{{ route('ip.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="service-content-modern">
                @forelse($newIpRegistrations as $ip)
                <div class="request-item-modern" style="border-color: #8b5cf6;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong style="color: #1e293b;">{{ Str::limit($ip->title, 40) }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $ip->user->name }} • {{ $ip->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">{{ $ip->ip_type }}</span>
                            <br>
                            <a href="{{ route('ip.manager.show', $ip) }}" class="btn btn-xs btn-secondary mt-1"><i class="fas fa-eye"></i> View</a>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No new IP registrations.</p>
                @endforelse
            </div>
        </div>

        <div class="service-card-modern">
            <div class="service-header-modern">
                <h3>©️ Copyright</h3>
                <a href="{{ route('copyright.manager.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="service-content-modern">
                @forelse($newCopyrights as $copyright)
                <div class="request-item-modern" style="border-color: #ef4444;">
                    <div class="d-flex justify-content-between align-items-start">
                        <div style="flex:1;">
                            <strong style="color: #1e293b;">{{ Str::limit($copyright->title, 40) }}</strong>
                            <br><small class="text-muted"><i class="fas fa-user"></i> {{ $copyright->user->name }} • {{ $copyright->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-danger">{{ $copyright->work_type }}</span>
                            <br>
                            <a href="{{ route('copyright.manager.show', $copyright) }}" class="btn btn-xs btn-secondary mt-1"><i class="fas fa-eye"></i> View</a>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-4"><i class="fas fa-inbox"></i> No new copyright requests.</p>
                @endforelse
            </div>
        </div>

        <div class="quick-stats-modern">
            <h4 style="margin: 0 0 1rem 0; font-weight: 600;"><i class="fas fa-chart-line"></i> Quick Stats</h4>
            <div class="stat-row-modern">
                <strong>Total Clients:</strong>
                <span style="font-size: 1.25rem; font-weight: 700;">{{ $stats['total_clients'] }}</span>
            </div>
            <div class="stat-row-modern">
                <strong>Active Projects:</strong>
                <span style="font-size: 1.25rem; font-weight: 700;">{{ $stats['active_projects'] }}</span>
            </div>
            <div class="stat-row-modern">
                <strong>Pending Reviews:</strong>
                <span style="font-size: 1.25rem; font-weight: 700;">{{ $stats['new_requests'] }}</span>
            </div>
            <div class="stat-row-modern">
                <strong>Completed This Month:</strong>
                <span style="font-size: 1.25rem; font-weight: 700;">{{ $stats['completed_month'] }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<style>
.btn-xs{padding:0.25rem 0.5rem;font-size:0.75rem;}
</style>
@endpush
