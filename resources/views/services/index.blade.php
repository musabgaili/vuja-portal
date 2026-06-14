@extends('layouts.dashboard')

@section('title', __('portal.services.index.page_title'))
@section('page-title', __('portal.services.index.page_heading'))

@section('content')
<div class="services-grid">
    <!-- Idea Generation -->
    <div class="service-card" style="border-left: 4px solid #f59e0b;">
        <div class="service-icon" style="background: #f59e0b;">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="service-content">
            <h3>{{ __('portal.services.index.idea_title') }}</h3>
            <p>{{ __('portal.services.index.idea_body') }}</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.idea_f1') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.idea_f2') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.idea_f3') }}</li>
            </ul>
            <a href="{{ route('ideas.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> {{ __('portal.services.index.idea_cta') }}
            </a>
        </div>
    </div>

    <!-- Consultation -->
    <div class="service-card" style="border-left: 4px solid #10b981;">
        <div class="service-icon" style="background: #10b981;">
            <i class="fas fa-comments"></i>
        </div>
        <div class="service-content">
            <h3>{{ __('portal.services.index.consultation_title') }}</h3>
            <p>{{ __('portal.services.index.consultation_body') }}</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.consultation_f1') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.consultation_f2') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.consultation_f3') }}</li>
            </ul>
            <a href="{{ route('consultations.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> {{ __('portal.services.index.consultation_cta') }}
            </a>
        </div>
    </div>

    <!-- Research & IP -->
    <div class="service-card" style="border-left: 4px solid #3b82f6;">
        <div class="service-icon" style="background: #3b82f6;">
            <i class="fas fa-search"></i>
        </div>
        <div class="service-content">
            <h3>{{ __('portal.services.index.research_title') }}</h3>
            <p>{{ __('portal.services.index.research_body') }}</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.research_f1') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.research_f2') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.research_f3') }}</li>
            </ul>
            <a href="{{ route('research.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> {{ __('portal.services.index.research_cta') }}
            </a>
        </div>
    </div>

    <!-- IP Registration -->
    <div class="service-card" style="border-left: 4px solid #0C7075;">
        <div class="service-icon" style="background: #0C7075;">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="service-content">
            <h3>{{ __('portal.services.index.ip_title') }}</h3>
            <p>{{ __('portal.services.index.ip_body') }}</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.ip_f1') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.ip_f2') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.ip_f3') }}</li>
            </ul>
            <a href="{{ route('ip.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> {{ __('portal.services.index.ip_cta') }}
            </a>
        </div>
    </div>

    <!-- Copyright Registration -->
    <div class="service-card" style="border-left: 4px solid #ec4899;">
        <div class="service-icon" style="background: #ec4899;">
            <i class="fas fa-copyright"></i>
        </div>
        <div class="service-content">
            <h3>{{ __('portal.services.index.copyright_title') }}</h3>
            <p>{{ __('portal.services.index.copyright_body') }}</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.copyright_f1') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.copyright_f2') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.copyright_f3') }}</li>
            </ul>
            <a href="{{ route('copyright.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> {{ __('portal.services.index.copyright_cta') }}
            </a>
        </div>
    </div>

    <!-- Prototype Development -->
    <div class="service-card" style="border-left: 4px solid #6366f1;">
        <div class="service-icon" style="background: #6366f1;">
            <i class="fas fa-cube"></i>
        </div>
        <div class="service-content">
            <h3>{{ __('portal.services.index.prototype_title') }}</h3>
            <p>{{ __('portal.services.index.prototype_body') }}</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.prototype_f1') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.prototype_f2') }}</li>
                <li><i class="fas fa-check"></i> {{ __('portal.services.index.prototype_f3') }}</li>
            </ul>
            <a href="{{ route('prototypes.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> {{ __('portal.services.index.prototype_cta') }}
            </a>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.services.index.how_title') }}</h3>
    </div>
    <div class="card-content">
        <div class="row">
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-number">1</div>
                    <h4>{{ __('portal.services.index.step1_title') }}</h4>
                    <p>{{ __('portal.services.index.step1_body') }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-number">2</div>
                    <h4>{{ __('portal.services.index.step2_title') }}</h4>
                    <p>{{ __('portal.services.index.step2_body') }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-number">3</div>
                    <h4>{{ __('portal.services.index.step3_title') }}</h4>
                    <p>{{ __('portal.services.index.step3_body') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: var(--space-lg);
    margin-bottom: var(--space-xl);
}

.service-card {
    background: var(--card-bg);
    border-radius: var(--border-radius);
    padding: var(--space-lg);
    box-shadow: var(--shadow-light);
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-medium);
}

.service-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: var(--font-size-2xl);
    margin-bottom: var(--space-md);
}

.service-content h3 {
    font-size: var(--font-size-lg);
    font-weight: 600;
    margin-bottom: var(--space-sm);
    color: var(--text-color);
}

.service-content p {
    color: var(--gray-600);
    margin-bottom: var(--space-md);
    line-height: 1.6;
}

.service-features {
    list-style: none;
    padding: 0;
    margin: 0 0 var(--space-md) 0;
}

.service-features li {
    padding: var(--space-xs) 0;
    color: var(--gray-700);
}

.service-features i {
    color: var(--success-color);
    margin-right: var(--space-xs);
}

.btn-block {
    width: 100%;
    justify-content: center;
}

.step-box {
    text-align: center;
    padding: var(--space-lg);
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-xl);
    font-weight: 600;
    margin: 0 auto var(--space-md) auto;
}

.step-box h4 {
    margin-bottom: var(--space-sm);
    color: var(--text-color);
}

.step-box p {
    color: var(--gray-600);
    line-height: 1.5;
}
</style>
@endpush
