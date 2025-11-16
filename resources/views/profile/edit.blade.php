@extends('layouts.dashboard')
@section('title', 'Edit Profile')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-user-edit"></i> Edit Profile Information
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Full Name *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">Phone Number</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 123-4567">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <div class="input-group">
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                            <a href="{{ route('profile.security') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i> Change Email
                            </a>
                        </div>
                        <small class="form-text text-muted">
                            Email changes require password verification and re-verification.
                        </small>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Profile Tips</h5>
            </div>
            <div class="card-content">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        Keep your name up to date for better communication
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        Add a phone number for urgent notifications
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        Email changes require verification
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success"></i>
                        Use a strong password for security
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-shield-alt"></i> Security</h5>
            </div>
            <div class="card-content">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-warning">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-info">
                        <i class="fas fa-envelope"></i> Update Email
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-header.bg-gradient-primary {
    background: linear-gradient(135deg, #198754 0%, #0d6e3f 100%) !important;
}

.form-control:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.btn-outline-warning:hover,
.btn-outline-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
