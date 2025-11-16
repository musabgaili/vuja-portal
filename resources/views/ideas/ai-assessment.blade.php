@extends('layouts.dashboard')

@section('title', 'AI Assessment')
@section('page-title', 'AI-Powered Idea Assessment')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🤖 AI Assessment - {{ $idea->title }}</h3>
        <span class="token-balance">
            <i class="fas fa-coins"></i> Tokens Used: {{ $idea->tokens_used }}
        </span>
    </div>
    <div class="card-content">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>AI Assessment Module</strong>
            <p class="mb-0">This feature requires integration with external AI APIs for visualization and text analysis. The system is ready, but external API keys need to be configured.</p>
        </div>

        <!-- AI Options Form -->
        <form method="POST" action="{{ route('ideas.ai-assessment.process', $idea) }}">
            @csrf
            
            <div class="ai-options-grid">
                <!-- Visualization AI -->
                <div class="ai-option-card">
                    <div class="option-icon" style="background: #8b5cf6;">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h4>Visualization AI</h4>
                    <p>Generate visual concepts and mockups of your idea</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ai_options[]" value="visualization" id="vis">
                        <label class="form-check-label" for="vis">
                            Enable Visualization (10 tokens)
                        </label>
                    </div>
                </div>

                <!-- Text Analysis AI -->
                <div class="ai-option-card">
                    <div class="option-icon" style="background: #ec4899;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h4>Text Analysis AI</h4>
                    <p>Deep analysis of your idea's market potential and feasibility</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ai_options[]" value="text_analysis" id="text">
                        <label class="form-check-label" for="text">
                            Enable Text Analysis (10 tokens)
                        </label>
                    </div>
                </div>

                <!-- Market Research AI -->
                <div class="ai-option-card">
                    <div class="option-icon" style="background: #10b981;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Market Research AI</h4>
                    <p>Analyze market trends and competition for your idea</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ai_options[]" value="market_research" id="market">
                        <label class="form-check-label" for="market">
                            Enable Market Research (15 tokens)
                        </label>
                    </div>
                </div>

                <!-- Business Model AI -->
                <div class="ai-option-card">
                    <div class="option-icon" style="background: #f59e0b;">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h4>Business Model AI</h4>
                    <p>Generate potential business models and revenue streams</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ai_options[]" value="business_model" id="business">
                        <label class="form-check-label" for="business">
                            Enable Business Model (15 tokens)
                        </label>
                    </div>
                </div>
            </div>

            <!-- Token Count -->
            <div class="form-group mt-4">
                <label class="form-label">Total Tokens to Use *</label>
                <input type="number" name="token_count" class="form-control @error('token_count') is-invalid @enderror" 
                       value="{{ old('token_count', 10) }}" min="1" max="100" required>
                <small class="form-text text-muted">Each AI tool consumes tokens. Choose wisely!</small>
                @error('token_count')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Token Wallet Info -->
            <div class="alert alert-info">
                <i class="fas fa-wallet me-2"></i>
                <strong>Token Wallet System</strong>
                <p class="mb-0 mt-2">The token wallet and recharge system requires payment gateway integration. This will be available in the next phase.</p>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-robot"></i> Run AI Assessment
                </button>
                <a href="{{ route('ideas.show', $idea) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </form>

        <!-- AI Results (if available) -->
        @if($idea->ai_assessment_data)
        <div class="ai-results mt-4">
            <h4>AI Assessment Results</h4>
            <div class="results-grid">
                @foreach($idea->ai_assessment_data as $key => $value)
                <div class="result-card">
                    <h5>{{ ucfirst(str_replace('_', ' ', $key)) }}</h5>
                    <p>{{ $value }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.token-balance {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    background: var(--primary-color);
    color: white;
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-md);
    font-weight: 600;
}

.ai-options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--space-lg);
    margin: var(--space-xl) 0;
}

.ai-option-card {
    background: var(--bg-tertiary);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.ai-option-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.option-icon {
    width: 60px;
    height: 60px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: var(--font-size-xl);
    margin-bottom: var(--space-md);
}

.ai-option-card h4 {
    margin-bottom: var(--space-sm);
    color: var(--text-color);
}

.ai-option-card p {
    color: var(--gray-600);
    margin-bottom: var(--space-md);
    font-size: var(--font-size-sm);
}

.ai-results {
    background: var(--bg-tertiary);
    padding: var(--space-xl);
    border-radius: var(--radius-md);
}

.results-grid {
    display: grid;
    gap: var(--space-md);
    margin-top: var(--space-md);
}

.result-card {
    background: white;
    padding: var(--space-md);
    border-radius: var(--radius-md);
    border-left: 4px solid var(--primary-color);
}

.result-card h5 {
    color: var(--primary-color);
    margin-bottom: var(--space-sm);
}

.result-card p {
    color: var(--gray-700);
    margin: 0;
}
</style>
@endpush

