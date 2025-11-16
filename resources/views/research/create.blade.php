@extends('layouts.dashboard')
@section('title', 'New Research Request')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">🔍 Research & IP Request</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('research.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Research Title *</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Research Topic *</label>
                <textarea name="research_topic" rows="4" class="form-control @error('research_topic') is-invalid @enderror" placeholder="What do you need researched?" required>{{ old('research_topic') }}</textarea>
                @error('research_topic')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Additional Details</label>
                <textarea name="research_details" rows="3" class="form-control" placeholder="Any specific requirements or scope...">{{ old('research_details') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Relevant Links</label>
                <textarea name="relevant_links" rows="3" class="form-control" placeholder="URLs, references...">{{ old('relevant_links') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Upload Files</label>
                <input type="file" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.png">
                <small class="text-muted">Max 10MB per file</small>
            </div>
            <div class="alert alert-warning">
                <i class="fas fa-file-signature me-2"></i>
                <strong>NDA/SLA Signing Required:</strong> Before we begin research, you'll need to sign our NDA and SLA documents (Digital signature integration coming soon).
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
        </form>
    </div>
</div>
@endsection

