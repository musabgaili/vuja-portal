@extends('layouts.dashboard')

@section('title', __('portal.consultations.create.title'))
@section('page-title', __('portal.consultations.create.page_title'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">💬 {{ __('portal.consultations.create.heading') }}</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('portal.consultations.create.back') }}
        </a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('consultations.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">{{ __('portal.consultations.create.category') }} *</label>
                <select name="category" class="form-control @error('category') is-invalid @enderror" required>
                    <option value="">{{ __('portal.consultations.create.select_category') }}</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.consultations.create.consultation_title') }} *</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" placeholder="{{ __('portal.consultations.create.title_placeholder') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.consultations.create.description') }} *</label>
                <textarea name="description" rows="6" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="{{ __('portal.consultations.create.description_placeholder') }}" required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('portal.consultations.create.specific_questions') }}</label>
                <textarea name="specific_questions" rows="4" class="form-control @error('specific_questions') is-invalid @enderror" 
                          placeholder="{{ __('portal.consultations.create.specific_questions_placeholder') }}">{{ old('specific_questions') }}</textarea>
                @error('specific_questions')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>{{ __('portal.consultations.create.what_next') }}:</strong> {{ __('portal.consultations.create.what_next_body') }}
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> {{ __('portal.consultations.create.submit_request') }}
                </button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> {{ __('portal.consultations.create.cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

