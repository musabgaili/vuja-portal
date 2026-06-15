@extends(auth()->user()?->isInternal() ? 'layouts.internal-dashboard' : 'layouts.dashboard')
@section('title', __('portal.profile.security_settings_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('profile.show') }}">{{ __('portal.profile.profile') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.profile.security') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Change Password -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-warning text-white">
                <h3 class="mb-0">
                    <i class="fas fa-key"></i> {{ __('portal.profile.change_password') }}
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update-password') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.current_password') }} *</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.new_password') }} *</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            {{ __('portal.profile.password_min_8') }}
                        </small>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.confirm_new_password') }} *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> {{ __('portal.profile.update_password') }}
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Email -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-info text-white">
                <h3 class="mb-0">
                    <i class="fas fa-envelope"></i> {{ __('portal.profile.change_email_address') }}
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update-email') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.current_email') }}</label>
                        <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.new_email_address') }} *</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.profile.current_password') }} *</label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            {{ __('portal.profile.required_to_verify_identity') }}
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-envelope"></i> {{ __('portal.profile.update_email') }}
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Update Phone -->
        <div class="card mb-4">
            <div class="card-header bg-gradient-secondary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-phone"></i> {{ __('portal.profile.update_phone_number') }}
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update-phone') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.auth.phone_number') }}</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                               value="{{ old('phone', $user->phone) }}" placeholder="{{ __('portal.profile.phone_placeholder') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            {{ __('portal.profile.phone_optional_help') }}
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-phone"></i> {{ __('portal.profile.update_phone') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-shield-alt"></i> {{ __('portal.profile.security_tips') }}</h5>
            </div>
            <div class="card-content">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_strong_unique_password') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_do_not_reuse_passwords') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_keep_email_updated') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_add_phone_recovery') }}
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_logout_shared_computers') }}
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> {{ __('portal.profile.account_status') }}</h5>
            </div>
            <div class="card-content">
                <div class="mb-2">
                    <strong>{{ __('portal.profile.email') }}:</strong>
                    @if($user->email_verified_at)
                        <span class="badge bg-success">{{ __('portal.profile.verified') }}</span>
                    @else
                        <span class="badge bg-warning">{{ __('portal.profile.unverified') }}</span>
                    @endif
                </div>
                <div class="mb-2">
                    <strong>{{ __('portal.profile.phone') }}:</strong>
                    @if($user->phone)
                        <span class="badge bg-success">{{ __('portal.profile.added') }}</span>
                    @else
                        <span class="badge bg-secondary">{{ __('portal.profile.not_added') }}</span>
                    @endif
                </div>
                <div class="mb-0">
                    <strong>{{ __('portal.profile.member_since') }}:</strong>
                    <br><small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Brand the section headers in the portal teal palette (was pink/cyan/green). */
.card-header.bg-gradient-warning {
    background: linear-gradient(135deg, #0F969C 0%, #0C7075 100%) !important;
}

.card-header.bg-gradient-info {
    background: linear-gradient(135deg, #0C7075 0%, #294D61 100%) !important;
}

.card-header.bg-gradient-secondary {
    background: linear-gradient(135deg, #294D61 0%, #0C7075 100%) !important;
}

.form-control:focus {
    border-color: #2C3F43;
    box-shadow: 0 0 0 0.2rem rgba(28, 87, 95, 0.25);
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
