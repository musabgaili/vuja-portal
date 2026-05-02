@extends('layouts.dashboard')
@section('title', __('portal.profile.my_profile_title'))

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-user"></i> {{ __('portal.profile.profile_information') }}
                </h3>
            </div>
            <div class="card-content">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">{{ __('portal.auth.full_name') }}</label>
                            <p class="mb-0">{{ $user->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">{{ __('portal.email_address') }}</label>
                            <p class="mb-0">
                                {{ $user->email }}
                                @if($user->email_verified_at)
                                    <span class="badge bg-success ms-2">
                                        <i class="fas fa-check"></i> {{ __('portal.profile.verified') }}
                                    </span>
                                @else
                                    <span class="badge bg-warning ms-2">
                                        <i class="fas fa-exclamation-triangle"></i> {{ __('portal.profile.unverified') }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">{{ __('portal.auth.phone_number') }}</label>
                            <p class="mb-0">{{ $user->phone ?: __('portal.profile.not_provided') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">{{ __('portal.profile.account_type') }}</label>
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
                            <label class="form-label fw-bold text-muted">{{ __('portal.profile.member_since') }}</label>
                            <p class="mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <label class="form-label fw-bold text-muted">{{ __('portal.profile.last_updated') }}</label>
                            <p class="mb-0">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> {{ __('portal.profile.edit_profile') }}
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-shield-alt"></i> {{ __('portal.profile.security_settings') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-cog"></i> {{ __('portal.internal.quick_actions') }}</h5>
            </div>
            <div class="card-content">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-edit"></i> {{ __('portal.profile.update_profile') }}
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-warning">
                        <i class="fas fa-key"></i> {{ __('portal.profile.change_password') }}
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-info">
                        <i class="fas fa-envelope"></i> {{ __('portal.profile.update_email') }}
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-phone"></i> {{ __('portal.profile.update_phone') }}
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="fas fa-exclamation-triangle"></i> {{ __('portal.profile.danger_zone') }}</h5>
            </div>
            <div class="card-content">
                <p class="text-muted small mb-3">
                    {{ __('portal.profile.delete_account_warning_body') }}
                </p>
                <button class="btn btn-danger btn-sm" onclick="showDeleteModal()">
                    <i class="fas fa-trash"></i> {{ __('portal.profile.delete_account') }}
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
                    <i class="fas fa-exclamation-triangle"></i> {{ __('portal.profile.delete_account') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.delete-account') }}">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>{{ __('portal.profile.warning') }}</strong> {{ __('portal.profile.delete_account_modal_body') }}
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.current_password') }}</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.type_delete_to_confirm') }}</label>
                        <input type="text" name="confirmation" class="form-control" placeholder="{{ __('portal.profile.delete_word') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> {{ __('portal.profile.delete_account') }}
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
    background: linear-gradient(135deg, #1C575F 0%, #153d44 100%) !important;
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
