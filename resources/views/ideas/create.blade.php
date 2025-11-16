@extends('layouts.dashboard')

@section('title', 'New Idea Request')
@section('page-title', 'Submit Your Idea')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">💡 Idea Generation Request</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Services
        </a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('ideas.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Client Type *</label>
                        <select name="client_type" class="form-control @error('client_type') is-invalid @enderror" required>
                            <option value="">Select your type</option>
                            <option value="individual" {{ old('client_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="company" {{ old('client_type') === 'company' ? 'selected' : '' }}>Company</option>
                        </select>
                        @error('client_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Idea Status *</label>
                        <select name="idea_status" class="form-control @error('idea_status') is-invalid @enderror" required>
                            <option value="">Select current status</option>
                            <option value="seeking_around" {{ old('idea_status') === 'seeking_around' ? 'selected' : '' }}>Seeking Around</option>
                            <option value="ready" {{ old('idea_status') === 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="running_project" {{ old('idea_status') === 'running_project' ? 'selected' : '' }}>Running Project</option>
                            <option value="concept_only" {{ old('idea_status') === 'concept_only' ? 'selected' : '' }}>Concept Only</option>
                        </select>
                        @error('idea_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Idea Title *</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" placeholder="Give your idea a catchy title" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Idea Description *</label>
                <textarea name="description" rows="6" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="Describe your idea in detail (minimum 50 characters)..." required>{{ old('description') }}</textarea>
                <small class="form-text text-muted">Be as detailed as possible to help us understand your vision</small>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Target Market</label>
                <input type="text" name="target_market" class="form-control @error('target_market') is-invalid @enderror" 
                       value="{{ old('target_market') }}" placeholder="Who is your target audience?">
                @error('target_market')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Problem It Solves</label>
                <textarea name="problem_solving" rows="3" class="form-control @error('problem_solving') is-invalid @enderror" 
                          placeholder="What problem does your idea solve?">{{ old('problem_solving') }}</textarea>
                @error('problem_solving')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Unique Value Proposition</label>
                <textarea name="unique_value" rows="3" class="form-control @error('unique_value') is-invalid @enderror" 
                          placeholder="What makes your idea unique?">{{ old('unique_value') }}</textarea>
                @error('unique_value')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Next Steps:</strong> After submission, you can enhance your idea with our AI assessment tools, negotiate pricing with our team, and move forward with implementation.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Idea
                </button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

