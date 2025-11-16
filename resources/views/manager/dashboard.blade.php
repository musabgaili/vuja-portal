@extends('layouts.internal-dashboard')

@section('title', 'Manager Dashboard')
@section('page-title', 'Service Management Dashboard')

@section('content')
<!-- Service Overview Stats -->
<div class="dashboard-grid mb-4">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">💡 Ideas</h3>
            <div class="widget-icon" style="background: #f59e0b;">
                <i class="fas fa-lightbulb"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IdeaRequest::count() }}</span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IdeaRequest::where('status', 'negotiation')->count() }}</span>
                    <span class="stat-label">Negotiating</span>
                </div>
            </div>
            <a href="{{ route('ideas.manager.index') }}" class="btn btn-sm btn-secondary mt-2">View All</a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">💬 Consultations</h3>
            <div class="widget-icon" style="background: #10b981;">
                <i class="fas fa-comments"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ConsultationRequest::count() }}</span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ConsultationRequest::where('status', 'assigned')->count() }}</span>
                    <span class="stat-label">Assigned</span>
                </div>
            </div>
            <a href="{{ route('consultations.manager.index') }}" class="btn btn-sm btn-secondary mt-2">View All</a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">🔍 Research</h3>
            <div class="widget-icon" style="background: #3b82f6;">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ResearchRequest::count() }}</span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ResearchRequest::where('status', 'in_progress')->count() }}</span>
                    <span class="stat-label">Active</span>
                </div>
            </div>
            <a href="{{ route('research.manager.index') }}" class="btn btn-sm btn-secondary mt-2">View All</a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">📄 IP & ©️</h3>
            <div class="widget-icon" style="background: #8b5cf6;">
                <i class="fas fa-certificate"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IpRegistration::count() + \App\Models\CopyrightRegistration::count() }}</span>
                    <span class="stat-label">Total</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IpRegistration::whereNotNull('registration_number')->count() + \App\Models\CopyrightRegistration::whereNotNull('copyright_number')->count() }}</span>
                    <span class="stat-label">Registered</span>
                </div>
            </div>
            <a href="{{ route('ip.manager.index') }}" class="btn btn-sm btn-secondary mt-2">View All</a>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📊 Recent Service Requests</h3>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Title</th>
                        <th>Client</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\IdeaRequest::latest()->take(5)->get() as $idea)
                    <tr>
                        <td><i class="fas fa-lightbulb text-warning"></i> Idea</td>
                        <td>{{ $idea->title }}</td>
                        <td>{{ $idea->user->name }}</td>
                        <td><span class="status-badge {{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span></td>
                        <td>{{ $idea->created_at->format('M d') }}</td>
                        <td><a href="{{ route('ideas.show', $idea) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @endforeach
                    @foreach(\App\Models\ConsultationRequest::latest()->take(3)->get() as $consultation)
                    <tr>
                        <td><i class="fas fa-comments text-success"></i> Consultation</td>
                        <td>{{ $consultation->title }}</td>
                        <td>{{ $consultation->user->name }}</td>
                        <td><span class="status-badge {{ $consultation->getStatusBadgeColor() }}">{{ $consultation->getStatusLabel() }}</span></td>
                        <td>{{ $consultation->created_at->format('M d') }}</td>
                        <td><a href="{{ route('consultations.show', $consultation) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

