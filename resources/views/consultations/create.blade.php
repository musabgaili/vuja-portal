@extends('layouts.dashboard')

@section('title', 'New Consultation Request')
@section('page-title', 'Request Expert Consultation')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">💬 Consultation Request</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('consultations.store') }}">
            @csrf
            
            <div class="form-group">
                <label class="form-label">Consultation Category *</label>
                <select name="category" class="form-control @error('category') is-invalid @enderror" required>
                    <option value="">Select a category...</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Consultation Title *</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" placeholder="Brief title for your consultation" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" rows="6" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="Describe what you need help with (minimum 20 characters)..." required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Specific Questions</label>
                <textarea name="specific_questions" rows="4" class="form-control @error('specific_questions') is-invalid @enderror" 
                          placeholder="List any specific questions you want answered...">{{ old('specific_questions') }}</textarea>
                @error('specific_questions')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>What happens next:</strong> Based on your selected category, we'll automatically match you with the most suitable expert. They will send you a meeting invitation within 24-48 hours.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
                <a href="{{ route('services.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

