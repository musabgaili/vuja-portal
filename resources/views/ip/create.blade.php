@extends('layouts.dashboard')
@section('title', 'IP Registration')
@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📄 IP Registration Request</h3>
        <a href="{{ route('services.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card-content">
        <form method="POST" action="{{ route('ip.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">IP Type *</label>
                <select name="ip_type" class="form-control" required>
                    <option value="">Select type...</option>
                    @foreach($ipTypes as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="ip_description" rows="6" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Supporting Documents</label>
                <input type="file" name="documents[]" class="form-control" multiple>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-calendar me-2"></i> After submission, you can book a meeting directly with our IP specialist.
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
        </form>
    </div>
</div>
@endsection

