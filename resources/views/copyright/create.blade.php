@extends('layouts.dashboard')
@section('title', 'Copyright Registration')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">©️ Copyright Registration Request</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('copyright.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Work Type *</label>
                <select name="work_type" class="form-control" required>
                    <option value="">Select type...</option>
                    @foreach($workTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Work Title *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Work Description *</label>
                <textarea name="work_description" rows="6" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Upload Work Files</label>
                <input type="file" name="files[]" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.png,.mp3,.mp4">
                <small class="text-muted">Upload your creative work (max 20MB per file)</small>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-calendar me-2"></i> After submission, you can book a meeting directly with our copyright specialist.
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
        </form>
    </div>
</div>
@endsection

