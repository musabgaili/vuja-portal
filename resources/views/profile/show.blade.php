@extends('layouts.dashboard')
@section('title', 'My Profile')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-user"></i> Profile Information
                </h3>
            </div>
            <div class="card-content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">Full Name</label>
                            <p class="mb-0">{{ $user->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">Email Address</label>
                            <p class="mb-0">
                                {{ $user->email }}
                                @if($user->email_verified_at)
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-check"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning ms-2">
                                        <i class="fas fa-exclamation-triangle"></i> Unverified
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">Phone Number</label>
                            <p class="mb-0">{{ $user->phone ?: 'Not provided' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">Account Type</label>
                            <p class="mb-0">
                                <span class="badge bg-info">{{ ucfirst($user->type) }}</span>
                                @if($user->role)
                                    <span class="badge bg-secondary ms-1">{{ ucfirst(str_replace('_', ' ', $user->role->value)) }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">Member Since</label>
                            <p class="mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">Last Updated</label>
                            <p class="mb-0">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-shield-alt"></i> Security Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-cog"></i> Quick Actions</h5>
            </div>
            <div class="card-content">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-edit"></i> Update Profile
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-warning">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-info">
                        <i class="fas fa-envelope"></i> Update Email
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-phone"></i> Update Phone
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="fas fa-exclamation-triangle"></i> Danger Zone</h5>
            </div>
            <div class="card-content">
                <p class="text-muted small mb-3">
                    Once you delete your account, there is no going back. Please be certain.
                </p>
                <button class="btn btn-danger btn-sm" onclick="showDeleteModal()">
                    <i class="fas fa-trash"></i> Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Delete Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.delete-account') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Warning!</strong> This action cannot be undone. This will permanently delete your account and remove all your data from our servers.
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Current Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Type "DELETE" to confirm</label>
                        <input type="text" name="confirmation" class="form-control" placeholder="DELETE" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showDeleteModal() {
    new bootstrap.Modal(document.getElementById('deleteAccountModal')).show();
}
</script>
@endpush

@push('styles')
<style>
.info-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.card-header.bg-gradient-primary {
    background: linear-gradient(135deg, #198754 0%, #0d6e3f 100%) !important;
}

.btn-outline-primary:hover,
.btn-outline-warning:hover,
.btn-outline-info:hover,
.btn-outline-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
