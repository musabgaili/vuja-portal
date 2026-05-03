@extends('layouts.internal-dashboard')
@section('title', __('portal.team.invite_team_member_title'))
@section('page-title', __('portal.team.invite_team_member_title'))

@section('breadcrumbs')
<li class="breadcrumb-item"><a href="{{ route('team.index') }}">{{ __('portal.team.breadcrumb_team') }}</a></li>
<li class="breadcrumb-item active">{{ __('portal.team.breadcrumb_invite_member') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h3>{{ __('portal.team.invite_new_member') }}</h3>
            </div>
            <div class="card-content">
                <form method="POST" action="{{ route('team.store') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label>{{ __('portal.auth.full_name') }} *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.email_address') }} *</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        <small class="text-muted">{{ __('portal.team.credentials_sent_to_email') }}</small>
                        @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.auth.phone_number') }}</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1234567890">
                        @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label>{{ __('portal.team.role_label') }} *</label>
                        <select name="role" class="form-control" required>
                            <option value="">{{ __('portal.team.select_role') }}</option>
                            <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>{{ __('portal.team.role_employee') }}</option>
                            <option value="manager" {{ old('role') === 'manager' ? 'selected' : '' }}>{{ __('portal.team.role_manager') }}</option>
                            <option value="project_manager" {{ old('role') === 'project_manager' ? 'selected' : '' }}>{{ __('portal.team.role_project_manager') }}</option>
                        </select>
                        @error('role')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>{{ __('portal.team.note_label') }}</strong> {{ __('portal.team.note_body') }}
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> {{ __('portal.team.send_invitation') }}
                        </button>
                        <a href="{{ route('team.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> {{ __('portal.team.cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

