@extends('layouts.dashboard')

@section('title', __('portal.client.requests.page_title'))
@section('page-title', __('portal.client.requests.page_heading'))

@section('content')
<!-- Summary Stats -->
<div class="dashboard-grid mb-4">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.client.requests.total_requests') }}</h3>
            <div class="widget-icon primary">
                <i class="fas fa-list"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['total'] }}</span>
                    <span class="stat-label">{{ __('portal.client.requests.all_time') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.client.requests.pending') }}</h3>
            <div class="widget-icon warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['pending'] }}</span>
                    <span class="stat-label">{{ __('portal.client.requests.awaiting_review') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.client.requests.in_progress') }}</h3>
            <div class="widget-icon info">
                <i class="fas fa-spinner"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['in_progress'] }}</span>
                    <span class="stat-label">{{ __('portal.stat_active') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.stat_completed') }}</h3>
            <div class="widget-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $summary['completed'] }}</span>
                    <span class="stat-label">{{ __('portal.client.requests.finished') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Service Type Breakdown -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.client.requests.by_service_type') }}</h3>
    </div>
    <div class="card-content">
        <div class="service-breakdown">
            <div class="service-stat" style="border-left-color: #0F969C;">
                <i class="fas fa-lightbulb" style="color: #0F969C;"></i>
                <div>
                    <strong>{{ $summary['ideas'] }}</strong>
                    <span>{{ __('portal.client.requests.ideas') }}</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #0C7075;">
                <i class="fas fa-comments" style="color: #0C7075;"></i>
                <div>
                    <strong>{{ $summary['consultations'] }}</strong>
                    <span>{{ __('portal.client.requests.consultations') }}</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #294D61;">
                <i class="fas fa-search" style="color: #294D61;"></i>
                <div>
                    <strong>{{ $summary['research'] }}</strong>
                    <span>{{ __('portal.client.requests.research') }}</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #2C3F43;">
                <i class="fas fa-file-contract" style="color: #2C3F43;"></i>
                <div>
                    <strong>{{ $summary['ip'] }}</strong>
                    <span>{{ __('portal.client.requests.ip_registration') }}</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #072E33;">
                <i class="fas fa-copyright" style="color: #072E33;"></i>
                <div>
                    <strong>{{ $summary['copyright'] }}</strong>
                    <span>{{ __('portal.client.requests.copyright') }}</span>
                </div>
            </div>
            <div class="service-stat" style="border-left-color: #0C7075;">
                <i class="fas fa-cubes" style="color: #0C7075;"></i>
                <div>
                    <strong>{{ $summary['threed'] ?? 0 }}</strong>
                    <span>{{ __('portal.client.requests.threed') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- All Requests Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.client.requests.all_my_requests') }}</h3>
        <div class="d-flex gap-2">
            <select class="form-control" style="width: auto;" onchange="filterByType(this.value)">
                <option value="">{{ __('portal.client.requests.all_services') }}</option>
                <option value="idea" {{ $typeFilter === 'idea' ? 'selected' : '' }}>{{ __('portal.client.requests.ideas') }}</option>
                <option value="consultation" {{ $typeFilter === 'consultation' ? 'selected' : '' }}>{{ __('portal.client.requests.consultations') }}</option>
                <option value="research" {{ $typeFilter === 'research' ? 'selected' : '' }}>{{ __('portal.client.requests.research') }}</option>
                <option value="ip" {{ $typeFilter === 'ip' ? 'selected' : '' }}>{{ __('portal.client.requests.ip_registration') }}</option>
                <option value="copyright" {{ $typeFilter === 'copyright' ? 'selected' : '' }}>{{ __('portal.client.requests.copyright') }}</option>
                <option value="threed" {{ $typeFilter === 'threed' ? 'selected' : '' }}>{{ __('portal.client.requests.threed') }}</option>
            </select>
            <select class="form-control" style="width: auto;" onchange="filterByStatus(this.value)">
                <option value="">{{ __('portal.client.requests.all_status') }}</option>
                <option value="submitted">{{ __('portal.client.requests.status_submitted') }}</option>
                <option value="negotiation">{{ __('portal.client.requests.status_negotiation') }}</option>
                <option value="approved">{{ __('portal.client.requests.status_approved') }}</option>
                <option value="in_progress">{{ __('portal.client.requests.status_in_progress') }}</option>
                <option value="completed">{{ __('portal.client.requests.status_completed') }}</option>
            </select>
        </div>
    </div>
    <div class="card-content">
        @if($allRequests->count() > 0)
            <div class="requests-list">
                @foreach($allRequests as $req)
                <div class="request-card mb-3" style="border-left: 4px solid {{ $req['type_color'] }};">
                    <div class="request-header">
                        <div class="d-flex align-center">
                            <div class="request-type-icon" style="background: {{ $req['type_color'] }};">
                                <i class="fas fa-{{ $req['type_icon'] }}"></i>
                            </div>
                            <div class="request-info">
                                <h4 class="mb-1">{{ $req['title'] }}</h4>
                                <p class="text-muted mb-1">
                                    <span class="badge" style="background: {{ $req['type_color'] }}; color: white;">
                                        {{ $req['type_label'] }}
                                    </span>
                                    • {{ __('portal.client.requests.submitted') }} {{ $req['created_at']->translatedFormat('M d, Y') }}
                                    @if($req['assigned_to'])
                                        • {{ __('portal.client.requests.assigned_to') }} {{ $req['assigned_to'] }}
                                    @endif
                                </p>
                                <p class="request-description">{{ Str::limit($req['description'], 120) }}</p>
                            </div>
                        </div>
                        <div class="request-meta">
                            <span class="status-badge {{ $req['status_color'] }}">
                                {{ $req['status_label'] }}
                            </span>
                            @if($req['has_quote'])
                            <div class="quote-badge">
                                <i class="fas fa-file-invoice"></i>
                                {{ number_format($req['quote_amount'], 2) }} {{ config('scope.currency','SAR') }}
                            </div>
                            @endif
                            @if(isset($req['meeting_date']) && $req['meeting_date'])
                            <div class="meeting-badge">
                                <i class="fas fa-calendar"></i>
                                {{ $req['meeting_date']->translatedFormat('M d') }}
                            </div>
                            @endif
                            @if(isset($req['registration_number']) && $req['registration_number'])
                            <div class="reg-badge">
                                <i class="fas fa-certificate"></i>
                                {{ $req['registration_number'] }}
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="request-footer">
                        <span class="text-muted">{{ __('portal.client.requests.last_updated') }} {{ $req['updated_at']->diffForHumans() }}</span>
                        <a href="{{ $req['view_url'] }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye"></i> {{ __('portal.client.requests.view_details') }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>{{ __('portal.client.requests.empty_title') }}</h4>
                <p class="text-muted">{{ __('portal.client.requests.empty_body') }}</p>
                <a href="{{ route('services.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus"></i> {{ __('portal.client.requests.request_new_service') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.service-breakdown {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: var(--space-md);
}

.service-stat {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
    border-left: 3px solid;
}

.service-stat i {
    font-size: var(--font-size-xl);
}

.service-stat strong {
    display: block;
    font-size: var(--font-size-lg);
    color: var(--text-color);
}

.service-stat span {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
}

.request-card {
    background: var(--card-bg);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    box-shadow: var(--shadow-light);
    transition: all 0.3s ease;
}

.request-card:hover {
    box-shadow: var(--shadow-medium);
    transform: translateY(-2px);
}

.request-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: var(--space-md);
}

.request-type-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: var(--font-size-lg);
    margin-right: var(--space-md);
    flex-shrink: 0;
}

.request-info h4 {
    font-size: var(--font-size-lg);
    color: var(--text-color);
    font-weight: 600;
}

.request-description {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    line-height: 1.5;
    margin: 0;
}

.request-meta {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
    align-items: flex-end;
}

.quote-badge, .meeting-badge, .reg-badge {
    padding: var(--space-xs) var(--space-sm);
    background: var(--primary-color);
    color: white;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: 600;
}

.meeting-badge {
    background: var(--success-color);
}

.reg-badge {
    background: var(--info-color);
}

.request-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: var(--space-md);
    border-top: 1px solid var(--gray-200);
}

.status-badge {
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-md);
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Semantic status tints — brand-harmonised (info/primary read as teal, not blue),
   theme-aware so they stay legible in dark mode. */
.status-badge.success { background: rgba(22,163,74,.12); color: #15803d; }
.status-badge.warning { background: rgba(217,119,6,.14); color: #b45309; }
.status-badge.info    { background: var(--primary-light); color: var(--primary-color); }
.status-badge.error   { background: rgba(220,38,38,.12); color: #b91c1c; }
.status-badge.primary { background: var(--primary-light); color: var(--primary-color); }
.status-badge.secondary { background: var(--bg-tertiary); color: var(--gray-600); }
[data-bs-theme="dark"] .status-badge.success { background: rgba(74,222,128,.16); color: #4ade80; }
[data-bs-theme="dark"] .status-badge.warning { background: rgba(251,191,36,.16); color: #fbbf24; }
[data-bs-theme="dark"] .status-badge.error   { background: rgba(248,113,113,.16); color: #f87171; }

.text-muted {
    color: var(--gray-500);
}

.gap-2 {
    gap: var(--space-sm);
}
</style>
@endpush

@push('scripts')
<script>
function filterByType(type) {
    const url = new URL(window.location);
    if (type) {
        url.searchParams.set('type', type);
    } else {
        url.searchParams.delete('type');
    }
    window.location.href = url.toString();
}

function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    window.location.href = url.toString();
}
</script>
@endpush
{{-- @endsection --}}

