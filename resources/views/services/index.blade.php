@extends('layouts.dashboard')

@section('title', 'Services')
@section('page-title', 'Request Our Services')

@section('content')
<div class="services-grid">
    <!-- Idea Generation -->
    <div class="service-card" style="border-left: 4px solid #f59e0b;">
        <div class="service-icon" style="background: #f59e0b;">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="service-content">
            <h3>Idea Generation</h3>
            <p>Transform your innovative ideas into actionable business concepts with our AI-powered ideation tools.</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> AI-Enhanced Ideation</li>
                <li><i class="fas fa-check"></i> Price Negotiation</li>
                <li><i class="fas fa-check"></i> Agreement & Payment</li>
            </ul>
            <a href="{{ route('ideas.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> Start Idea Request
            </a>
        </div>
    </div>

    <!-- Consultation -->
    <div class="service-card" style="border-left: 4px solid #10b981;">
        <div class="service-icon" style="background: #10b981;">
            <i class="fas fa-comments"></i>
        </div>
        <div class="service-content">
            <h3>Expert Consultation</h3>
            <p>Get expert advice and guidance from our experienced professionals across various domains.</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> Category-Based Matching</li>
                <li><i class="fas fa-check"></i> Direct Meeting Scheduling</li>
                <li><i class="fas fa-check"></i> Expert Recommendations</li>
            </ul>
            <a href="{{ route('consultations.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> Request Consultation
            </a>
        </div>
    </div>

    <!-- Research & IP -->
    <div class="service-card" style="border-left: 4px solid #3b82f6;">
        <div class="service-icon" style="background: #3b82f6;">
            <i class="fas fa-search"></i>
        </div>
        <div class="service-content">
            <h3>Research & IP</h3>
            <p>Comprehensive research and intellectual property services with NDA protection.</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> NDA & SLA Signing</li>
                <li><i class="fas fa-check"></i> In-Depth Research</li>
                <li><i class="fas fa-check"></i> Meeting Coordination</li>
            </ul>
            <a href="{{ route('research.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> Request Research
            </a>
        </div>
    </div>

    <!-- IP Registration -->
    <div class="service-card" style="border-left: 4px solid #8b5cf6;">
        <div class="service-icon" style="background: #8b5cf6;">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="service-content">
            <h3>IP Registration</h3>
            <p>Register your intellectual property including patents, trademarks, and designs.</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> Direct Meeting Booking</li>
                <li><i class="fas fa-check"></i> Filing Assistance</li>
                <li><i class="fas fa-check"></i> Registration Tracking</li>
            </ul>
            <a href="{{ route('ip.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> Register IP
            </a>
        </div>
    </div>

    <!-- Copyright Registration -->
    <div class="service-card" style="border-left: 4px solid #ec4899;">
        <div class="service-icon" style="background: #ec4899;">
            <i class="fas fa-copyright"></i>
        </div>
        <div class="service-content">
            <h3>Copyright Registration</h3>
            <p>Protect your creative works with our copyright registration services.</p>
            <ul class="service-features">
                <li><i class="fas fa-check"></i> Direct Meeting Booking</li>
                <li><i class="fas fa-check"></i> Copyright Filing</li>
                <li><i class="fas fa-check"></i> Work Protection</li>
            </ul>
            <a href="{{ route('copyright.create') }}" class="btn btn-primary btn-block">
                <i class="fas fa-arrow-right"></i> Register Copyright
            </a>
        </div>
    </div>
</div>

<!-- How It Works -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">How Our Services Work</h3>
    </div>
    <div class="card-content">
        <div class="row">
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-number">1</div>
                    <h4>Choose Service</h4>
                    <p>Select the service that best fits your needs from our comprehensive offerings.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-number">2</div>
                    <h4>Submit Request</h4>
                    <p>Fill out the service-specific form with your requirements and details.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-box">
                    <div class="step-number">3</div>
                    <h4>Get Results</h4>
                    <p>Our team processes your request and delivers quality results.</p>
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
