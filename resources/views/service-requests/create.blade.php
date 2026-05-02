@extends('layouts.dashboard')

@section('title', __('portal.service_requests_page.create.page_title'))
@section('page-title', __('portal.service_requests_page.create.page_heading'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.service_requests_page.create.card_title') }}</h3>
        <a href="{{ route('service-requests.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('portal.service_requests_page.back_requests') }}
        </a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('service-requests.store') }}">
            @csrf
            
            <!-- Service Type -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.service_type') }}</label>
                <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                    <option value="">{{ __('portal.service_requests_page.create.select_service_type') }}</option>
                    <option value="idea" {{ old('type', $type) === 'idea' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.type_idea') }}</option>
                    <option value="consultation" {{ old('type') === 'consultation' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.type_consultation') }}</option>
                    <option value="research" {{ old('type') === 'research' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.type_research') }}</option>
                    <option value="copyright" {{ old('type') === 'copyright' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.type_copyright') }}</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Title -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.request_title') }}</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" placeholder="{{ __('portal.service_requests_page.create.title_placeholder') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.description') }}</label>
                <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="{{ __('portal.service_requests_page.create.description_placeholder') }}" required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Priority -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.priority') }}</label>
                <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                    <option value="">{{ __('portal.service_requests_page.create.select_priority') }}</option>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.priority_low') }}</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.priority_medium') }}</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.priority_high') }}</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>{{ __('portal.service_requests_page.create.priority_urgent') }}</option>
                </select>
                @error('priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Requirements -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.requirements') }}</label>
                <textarea name="requirements" rows="3" class="form-control @error('requirements') is-invalid @enderror" 
                          placeholder="{{ __('portal.service_requests_page.create.requirements_placeholder') }}">{{ old('requirements') }}</textarea>
                @error('requirements')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Budget Range -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.budget_optional') }}</label>
                <input type="text" name="budget_range" class="form-control @error('budget_range') is-invalid @enderror" 
                       value="{{ old('budget_range') }}" placeholder="{{ __('portal.service_requests_page.create.budget_placeholder') }}">
                @error('budget_range')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Timeline -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.timeline_optional') }}</label>
                <input type="text" name="timeline" class="form-control @error('timeline') is-invalid @enderror" 
                       value="{{ old('timeline') }}" placeholder="{{ __('portal.service_requests_page.create.timeline_placeholder') }}">
                @error('timeline')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Additional Information -->
            <div class="form-group">
                <label class="form-label">{{ __('portal.service_requests_page.create.additional_info') }}</label>
                <textarea name="additional_info" rows="3" class="form-control @error('additional_info') is-invalid @enderror" 
                          placeholder="{{ __('portal.service_requests_page.create.additional_placeholder') }}">{{ old('additional_info') }}</textarea>
                @error('additional_info')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- External API Alert -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>{{ __('portal.service_requests_page.create.integration_note_title') }}</strong> {{ __('portal.service_requests_page.create.integration_note_body') }}
            </div>

            <!-- Submit Button -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> {{ __('portal.service_requests_page.create.submit') }}
                </button>
                <a href="{{ route('service-requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ __('portal.service_requests_page.create.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
