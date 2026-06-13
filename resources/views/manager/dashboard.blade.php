@extends('layouts.internal-dashboard')

@section('title', __('portal.manager_legacy.title'))
@section('page-title', __('portal.manager_legacy.page_title'))

@section('content')
<!-- Service Overview Stats -->
<div class="dashboard-grid mb-4">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">💡 {{ __('portal.client.requests.ideas') }}</h3>
            <div class="widget-icon" style="background: #f59e0b;">
                <i class="fas fa-lightbulb"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IdeaRequest::count() }}</span>
                    <span class="stat-label">{{ __('portal.manager_legacy.total') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IdeaRequest::where('status', 'negotiation')->count() }}</span>
                    <span class="stat-label">{{ __('portal.manager_legacy.negotiating') }}</span>
                </div>
            </div>
            <a href="{{ route('ideas.manager.index') }}" class="btn btn-sm btn-secondary mt-2">{{ __('portal.internal.view_all') }}</a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">💬 {{ __('portal.client.requests.consultations') }}</h3>
            <div class="widget-icon" style="background: #10b981;">
                <i class="fas fa-comments"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ConsultationRequest::count() }}</span>
                    <span class="stat-label">{{ __('portal.manager_legacy.total') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ConsultationRequest::where('status', 'assigned')->count() }}</span>
                    <span class="stat-label">{{ __('portal.internal.label_assigned') }}</span>
                </div>
            </div>
            <a href="{{ route('consultations.manager.index') }}" class="btn btn-sm btn-secondary mt-2">{{ __('portal.internal.view_all') }}</a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">🔍 {{ __('portal.client.requests.research') }}</h3>
            <div class="widget-icon" style="background: #3b82f6;">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ResearchRequest::count() }}</span>
                    <span class="stat-label">{{ __('portal.manager_legacy.total') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\ResearchRequest::where('status', 'in_progress')->count() }}</span>
                    <span class="stat-label">{{ __('portal.stat_active') }}</span>
                </div>
            </div>
            <a href="{{ route('research.manager.index') }}" class="btn btn-sm btn-secondary mt-2">{{ __('portal.internal.view_all') }}</a>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">📄 {{ __('portal.client.requests.ip_registration') }} & ©️</h3>
            <div class="widget-icon" style="background: #0C7075;">
                <i class="fas fa-certificate"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IpRegistration::count() + \App\Models\CopyrightRegistration::count() }}</span>
                    <span class="stat-label">{{ __('portal.manager_legacy.total') }}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ \App\Models\IpRegistration::whereNotNull('registration_number')->count() + \App\Models\CopyrightRegistration::whereNotNull('copyright_number')->count() }}</span>
                    <span class="stat-label">{{ __('portal.manager_legacy.registered') }}</span>
                </div>
            </div>
            <a href="{{ route('ip.manager.index') }}" class="btn btn-sm btn-secondary mt-2">{{ __('portal.internal.view_all') }}</a>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📊 {{ __('portal.manager_legacy.recent_service_requests') }}</h3>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('portal.manager_legacy.col_service') }}</th>
                        <th>{{ __('portal.manager_legacy.col_title') }}</th>
                        <th>{{ __('portal.manager_legacy.col_client') }}</th>
                        <th>{{ __('portal.manager_legacy.col_status') }}</th>
                        <th>{{ __('portal.manager_legacy.col_date') }}</th>
                        <th>{{ __('portal.manager_legacy.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(\App\Models\IdeaRequest::latest()->take(5)->get() as $idea)
                    <tr>
                        <td><i class="fas fa-lightbulb text-warning"></i> {{ __('portal.manager_legacy.service_idea') }}</td>
                        <td>{{ $idea->title }}</td>
                        <td>{{ $idea->user->name }}</td>
                        <td><span class="status-badge {{ $idea->getStatusBadgeColor() }}">{{ $idea->getStatusLabel() }}</span></td>
                        <td>{{ $idea->created_at->format('M d') }}</td>
                        <td><a href="{{ route('ideas.show', $idea) }}" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @endforeach
                    @foreach(\App\Models\ConsultationRequest::latest()->take(3)->get() as $consultation)
                    <tr>
                        <td><i class="fas fa-comments text-success"></i> {{ __('portal.manager_legacy.service_consultation') }}</td>
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

