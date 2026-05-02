@extends('layouts.dashboard')

@section('title', __('portal.ideas.create.page_title'))
@section('page-title', __('portal.ideas.create.page_heading'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">💡 {{ __('portal.ideas.create.card_title') }}</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('portal.ideas.create.back_services') }}
        </a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('ideas.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.ideas.create.client_type') }}</label>
                        <select name="client_type" class="form-control @error('client_type') is-invalid @enderror" required>
                            <option value="">{{ __('portal.ideas.create.client_type_placeholder') }}</option>
                            <option value="individual" {{ old('client_type') === 'individual' ? 'selected' : '' }}>{{ __('portal.ideas.create.client_type_individual') }}</option>
                            <option value="company" {{ old('client_type') === 'company' ? 'selected' : '' }}>{{ __('portal.ideas.create.client_type_company') }}</option>
                        </select>
                        @error('client_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.ideas.create.idea_status') }}</label>
                        <select name="idea_status" class="form-control @error('idea_status') is-invalid @enderror" required>
                            <option value="">{{ __('portal.ideas.create.idea_status_placeholder') }}</option>
                            <option value="seeking_around" {{ old('idea_status') === 'seeking_around' ? 'selected' : '' }}>{{ __('portal.ideas.create.idea_status_seeking_around') }}</option>
                            <option value="ready" {{ old('idea_status') === 'ready' ? 'selected' : '' }}>{{ __('portal.ideas.create.idea_status_ready') }}</option>
                            <option value="running_project" {{ old('idea_status') === 'running_project' ? 'selected' : '' }}>{{ __('portal.ideas.create.idea_status_running_project') }}</option>
                            <option value="concept_only" {{ old('idea_status') === 'concept_only' ? 'selected' : '' }}>{{ __('portal.ideas.create.idea_status_concept_only') }}</option>
                        </select>
                        @error('idea_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.ideas.create.title') }}</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" placeholder="{{ __('portal.ideas.create.title_placeholder') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.ideas.create.description') }}</label>
                <textarea name="description" rows="6" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="{{ __('portal.ideas.create.description_placeholder') }}" required>{{ old('description') }}</textarea>
                <small class="form-text text-muted">{{ __('portal.ideas.create.description_hint') }}</small>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.ideas.create.target_market') }}</label>
                <input type="text" name="target_market" class="form-control @error('target_market') is-invalid @enderror" 
                       value="{{ old('target_market') }}" placeholder="{{ __('portal.ideas.create.target_market_placeholder') }}">
                @error('target_market')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.ideas.create.problem_solving') }}</label>
                <textarea name="problem_solving" rows="3" class="form-control @error('problem_solving') is-invalid @enderror" 
                          placeholder="{{ __('portal.ideas.create.problem_solving_placeholder') }}">{{ old('problem_solving') }}</textarea>
                @error('problem_solving')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.ideas.create.unique_value') }}</label>
                <textarea name="unique_value" rows="3" class="form-control @error('unique_value') is-invalid @enderror" 
                          placeholder="{{ __('portal.ideas.create.unique_value_placeholder') }}">{{ old('unique_value') }}</textarea>
                @error('unique_value')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>{{ __('portal.ideas.create.next_steps_title') }}</strong> {{ __('portal.ideas.create.next_steps_body') }}
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> {{ __('portal.ideas.create.submit') }}
                </button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ __('portal.ideas.create.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
