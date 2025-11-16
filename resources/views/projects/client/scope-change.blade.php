@extends('layouts.dashboard')
@section('title', 'Request Scope Change')
@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>Request Scope Change</h3>
                <p class="text-muted mb-0">Project: {{ $project->title }}</p>
            </div>
            <div class="card-content">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> Scope changes will be reviewed by the project manager. Approved changes may affect timeline and budget.
                </div>

                <form method="POST" action="{{ route('projects.client.scope-change.store', $project) }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>Change Title *</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g., Add mobile app feature">
                        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Detailed Description *</label>
                        <textarea name="description" rows="5" class="form-control" required placeholder="Describe what you want to change or add..."></textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>Justification / Business Need</label>
                        <textarea name="justification" rows="3" class="form-control" placeholder="Why is this change important?"></textarea>
                        @error('justification')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                        <a href="{{ route('projects.client.show', $project) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

