@extends('layouts.internal-dashboard')

@section('title', 'Create Service Type')
@section('page-title', 'Create New Service Request Type')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Service Request Type Details</h3>
        <a href="{{ route('stepper.service-types.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Service Types
        </a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('stepper.service-types.store') }}">
            @csrf
            
            <!-- Basic Information -->
            <div class="form-group">
                <label class="form-label">Service Type Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                       value="{{ old('name') }}" placeholder="e.g., Idea Generation, Research & IP" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror" 
                          placeholder="Brief description of this service type...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Visual Settings -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Icon (FontAwesome)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-icons"></i></span>
                            <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" 
                                   value="{{ old('icon', 'fas fa-cog') }}" placeholder="fas fa-lightbulb" required>
                        </div>
                        <small class="form-text text-muted">Use FontAwesome icon classes (e.g., fas fa-lightbulb)</small>
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Color</label>
                        <div class="input-group">
                            <input type="color" name="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                   value="{{ old('color', '#2563eb') }}" required>
                            <span class="input-group-text">#2563eb</span>
                        </div>
                        @error('color')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="is_active" 
                           value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        Active (Available to clients)
                    </label>
                </div>
            </div>

            <!-- Icon Preview -->
            <div class="form-group">
                <label class="form-label">Preview</label>
                <div class="preview-box p-3 rounded" style="background: var(--bg-tertiary);">
                    <div class="d-flex align-center">
                        <div class="preview-icon" style="background: {{ old('color', '#2563eb') }};">
                            <i class="{{ old('icon', 'fas fa-cog') }}"></i>
                        </div>
                        <div class="preview-text">
                            <h5 class="mb-1">{{ old('name', 'Service Type Name') }}</h5>
                            <p class="text-muted mb-0">{{ old('description', 'Service description will appear here...') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Service Type
                </button>
                <a href="{{ route('stepper.service-types.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.preview-icon {
    width: 50px;
    height: 50px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: var(--space-md);
    color: white;
    font-size: var(--font-size-lg);
}

.form-control-color {
    width: 60px;
    height: 38px;
    padding: 0;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
}

.gap-2 {
    gap: var(--space-sm);
}

.text-muted {
    color: var(--gray-500);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.querySelector('input[name="name"]');
    const descriptionInput = document.querySelector('textarea[name="description"]');
    const iconInput = document.querySelector('input[name="icon"]');
    const colorInput = document.querySelector('input[name="color"]');
    
    const previewName = document.querySelector('.preview-text h5');
    const previewDescription = document.querySelector('.preview-text p');
    const previewIcon = document.querySelector('.preview-icon i');
    const previewIconDiv = document.querySelector('.preview-icon');
    
    function updatePreview() {
        previewName.textContent = nameInput.value || 'Service Type Name';
        previewDescription.textContent = descriptionInput.value || 'Service description will appear here...';
        previewIcon.className = iconInput.value || 'fas fa-cog';
        previewIconDiv.style.background = colorInput.value || '#2563eb';
    }
    
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    iconInput.addEventListener('input', updatePreview);
    colorInput.addEventListener('input', updatePreview);
});
</script>
@endpush

