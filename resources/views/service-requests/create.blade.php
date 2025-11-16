@extends('layouts.dashboard')

@section('title', 'New Service Request')
@section('page-title', 'New Service Request')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Submit Service Request</h3>
        <a href="{{ route('service-requests.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Requests
        </a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('service-requests.store') }}">
            @csrf
            
            <!-- Service Type -->
            <div class="form-group">
                <label class="form-label">Service Type</label>
                <select name="type" class="form-control @error('type') is-invalid @enderror" required>
                    <option value="">Select Service Type</option>
                    <option value="idea" {{ old('type', $type) === 'idea' ? 'selected' : '' }}>Idea Generation</option>
                    <option value="consultation" {{ old('type') === 'consultation' ? 'selected' : '' }}>Consultation</option>
                    <option value="research" {{ old('type') === 'research' ? 'selected' : '' }}>Research & IP</option>
                    <option value="copyright" {{ old('type') === 'copyright' ? 'selected' : '' }}>Copyright Services</option>
                </select>
                @error('type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Title -->
            <div class="form-group">
                <label class="form-label">Request Title</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                       value="{{ old('title') }}" placeholder="Brief title for your request" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Description -->
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="Please provide a detailed description of your request..." required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Priority -->
            <div class="form-group">
                <label class="form-label">Priority Level</label>
                <select name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                    <option value="">Select Priority</option>
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low - No rush</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium - Standard timeline</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High - Urgent</option>
                    <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent - ASAP</option>
                </select>
                @error('priority')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Requirements -->
            <div class="form-group">
                <label class="form-label">Specific Requirements</label>
                <textarea name="requirements" rows="3" class="form-control @error('requirements') is-invalid @enderror" 
                          placeholder="List any specific requirements, deliverables, or constraints...">{{ old('requirements') }}</textarea>
                @error('requirements')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Budget Range -->
            <div class="form-group">
                <label class="form-label">Budget Range (Optional)</label>
                <input type="text" name="budget_range" class="form-control @error('budget_range') is-invalid @enderror" 
                       value="{{ old('budget_range') }}" placeholder="e.g., $5,000 - $10,000 or Flexible">
                @error('budget_range')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Timeline -->
            <div class="form-group">
                <label class="form-label">Desired Timeline (Optional)</label>
                <input type="text" name="timeline" class="form-control @error('timeline') is-invalid @enderror" 
                       value="{{ old('timeline') }}" placeholder="e.g., 2-3 weeks or ASAP">
                @error('timeline')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Additional Information -->
            <div class="form-group">
                <label class="form-label">Additional Information</label>
                <textarea name="additional_info" rows="3" class="form-control @error('additional_info') is-invalid @enderror" 
                          placeholder="Any other relevant information, context, or special considerations...">{{ old('additional_info') }}</textarea>
                @error('additional_info')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- External API Alert -->
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Note:</strong> Some features like digital signatures, calendar integration, and AI assessment tools require external API integrations and will be implemented in future phases.
            </div>

            <!-- Submit Button -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
                <a href="{{ route('service-requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
