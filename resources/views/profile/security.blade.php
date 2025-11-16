@extends('layouts.dashboard')
@section('title', 'Security Settings')

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Profile</a></li>
<li class="breadcrumb-item active">Security</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Change Password -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-warning text-white">
                <h3 class="mb-0">
                    <i class="fas fa-key"></i> Change Password
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update-password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Current Password *</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">New Password *</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Password must be at least 8 characters long.
                        </small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Confirm New Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Email -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-info text-white">
                <h3 class="mb-0">
                    <i class="fas fa-envelope"></i> Change Email Address
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update-email') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Current Email</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">New Email Address *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Current Password *</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Required to verify your identity.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-envelope"></i> Update Email
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Update Phone -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-secondary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-phone"></i> Update Phone Number
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update-phone') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Phone Number</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $user->phone) }}" placeholder="+1 (555) 123-4567">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Optional. Used for urgent notifications and account recovery.
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-phone"></i> Update Phone
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-shield-alt"></i> Security Tips</h5>
            </div>
            <div class="card-content">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        Use a strong, unique password
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        Don't reuse passwords from other sites
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        Keep your email address up to date
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        Add a phone number for recovery
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success"></i>
                        Log out from shared computers
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> Account Status</h5>
            </div>
            <div class="card-content">
                <div class="mb-2">
                    <strong>Email:</strong>
                    @if($user->email_verified_at)
                        <span class="badge bg-success">Verified</span>
                    @else
                        <span class="badge bg-warning">Unverified</span>
                    @endif
                </div>
                <div class="mb-2">
                    <strong>Phone:</strong>
                    @if($user->phone)
                        <span class="badge bg-success">Added</span>
                    @else
                        <span class="badge bg-secondary">Not Added</span>
                    @endif
                </div>
                <div class="mb-0">
                    <strong>Member Since:</strong>
                    <br><small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-header.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.card-header.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
}

.card-header.bg-gradient-secondary {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%) !important;
}

.form-control:focus {
    border-color: #198754;
    box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
