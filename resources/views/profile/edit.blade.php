@extends('layouts.dashboard')
@section('title', __('portal.profile.edit_profile_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('profile.show') }}">{{ __('portal.profile.profile') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.profile.edit') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-gradient-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-user-edit"></i> {{ __('portal.profile.edit_profile_information') }}
                </h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('portal.auth.full_name') }} *</label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold">{{ __('portal.auth.phone_number') }}</label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $user->phone) }}" placeholder="{{ __('portal.profile.phone_placeholder') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">{{ __('portal.email_address') }}</label>
                        <div class="input-group">
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly>
                            <a href="{{ route('profile.security') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-edit"></i> {{ __('portal.profile.change_email') }}
                            </a>
                        </div>
                        <small class="form-text text-muted">
                            {{ __('portal.profile.email_change_requires_verification') }}
                        </small>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('portal.profile.update_profile') }}
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> {{ __('portal.profile.profile_tips') }}</h5>
            </div>
            <div class="card-content">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_keep_name_updated') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_add_phone_notifications') }}
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_email_changes_require_verification') }}
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success"></i>
                        {{ __('portal.profile.tip_use_strong_password') }}
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-shield-alt"></i> {{ __('portal.profile.security') }}</h5>
            </div>
            <div class="card-content">
                <div class="d-grid gap-2">
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-warning">
                        <i class="fas fa-key"></i> {{ __('portal.profile.change_password') }}
                    </a>
                    <a href="{{ route('profile.security') }}" class="btn btn-outline-info">
                        <i class="fas fa-envelope"></i> {{ __('portal.profile.update_email') }}
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
    background: linear-gradient(135deg, #1C575F 0%, #153d44 100%) !important;
}

.form-control:focus {
    border-color: #1C575F;
    box-shadow: 0 0 0 0.2rem rgba(28, 87, 95, 0.25);
}

.btn-outline-warning:hover,
.btn-outline-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
