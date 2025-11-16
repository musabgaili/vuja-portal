@extends('layouts.dashboard')

@section('title', 'Request Services')
@section('page-title', 'Request Services')

@section('content')
<!-- Service Types Grid -->
<div class="service-types-grid">
    @if($serviceTypes->count() > 0)
        @foreach($serviceTypes as $serviceType)
        <div class="service-type-card" style="border-left: 4px solid {{ $serviceType->color }};">
            <div class="service-type-header">
                <div class="service-type-icon" style="background: {{ $serviceType->color }};">
                    <i class="{{ $serviceType->icon }}"></i>
                </div>
                <div class="service-type-info">
                    <h3 class="service-type-title">{{ $serviceType->name }}</h3>
                    @if($serviceType->description)
                    <p class="service-type-description">{{ $serviceType->description }}</p>
                    @endif
                </div>
            </div>
            
            <div class="service-type-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $serviceType->steps->count() }}</span>
                    <span class="stat-label">Steps</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">{{ $serviceType->serviceRequests->count() }}</span>
                    <span class="stat-label">Requests</span>
                </div>
            </div>
            
            <div class="service-type-actions">
                <a href="{{ route('stepper.client.create', $serviceType) }}" class="btn btn-primary btn-block">
                    <i class="fas fa-arrow-right"></i> Start Request
                </a>
            </div>
        </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
            <h4>No Services Available</h4>
            <p class="text-muted">No service request types are currently available. Please check back later.</p>
        </div>
    @endif
</div>

<!-- How It Works -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">How It Works</h3>
    </div>
    <div class="card-content">
        <div class="row">
            <div class="col-md-3">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <h5>Select Service</h5>
                    <p>Choose the type of service you need from our available options.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-item">
                    <div class="step-number">2</div>
                    <h5>Complete Steps</h5>
                    <p>Follow the guided process to provide all necessary information.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-item">
                    <div class="step-number">3</div>
                    <h5>Review & Submit</h5>
                    <p>Review your request and submit it for our team to process.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="step-item">
                    <div class="step-number">4</div>
                    <h5>Track Progress</h5>
                    <p>Monitor your request status and communicate with our team.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.service-types-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.service-type-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: var(--space-lg);
    box-shadow: var(--shadow-light);
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
}

.service-type-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-medium);
}

.service-type-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: var(--space-md);
}

.service-type-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: var(--space-md);
    color: white;
    font-size: var(--font-size-xl);
    flex-shrink: 0;
}

.service-type-title {
    font-size: var(--font-size-lg);
    font-weight: 600;
    margin: 0 0 var(--space-xs) 0;
    color: var(--text-color);
}

.service-type-description {
    color: var(--gray-600);
    margin: 0;
    line-height: 1.5;
}

.service-type-stats {
    display: flex;
    gap: var(--space-lg);
    margin-bottom: var(--space-md);
    padding: var(--space-sm) 0;
    border-top: 1px solid var(--gray-200);
    border-bottom: 1px solid var(--gray-200);
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--primary-color);
}

.stat-label {
    font-size: var(--font-size-sm);
    color: var(--gray-500);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.service-type-actions {
    margin-top: var(--space-md);
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.step-item {
    text-align: center;
    padding: var(--space-md);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin: 0 auto var(--space-sm) auto;
}

.step-item h5 {
    margin-bottom: var(--space-sm);
    color: var(--text-color);
}

.step-item p {
    color: var(--gray-600);
    font-size: var(--font-size-sm);
    line-height: 1.4;
}

.empty-state {
    text-align: center;
    padding: var(--space-2xl);
    grid-column: 1 / -1;
}

.empty-state i {
    opacity: 0.5;
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

