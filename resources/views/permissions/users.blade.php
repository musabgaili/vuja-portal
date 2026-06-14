@extends('layouts.internal-dashboard')

@section('title', __('portal.permissions.user_roles_title'))
@section('page-title', __('portal.permissions.assign_user_roles_permissions'))

@section('content')
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h3 class="card-title mb-0">{{ __('portal.permissions.user_role_management') }}</h3>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="btn-group" role="group">
                <a href="{{ route('permissions.users') }}" class="btn btn-sm {{ empty($clientsOnly ?? false) ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ __('portal.permissions.all_users_tab') }}
                </a>
                <a href="{{ route('permissions.users', ['filter' => 'clients']) }}" class="btn btn-sm {{ ! empty($clientsOnly ?? false) ? 'btn-primary' : 'btn-outline-primary' }}">
                    {{ __('portal.permissions.portal_clients_tab') }}
                </a>
            </div>
            <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> {{ __('portal.permissions.back') }}
            </a>
        </div>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('portal.permissions.user') }}</th>
                        <th>{{ __('portal.email_address') }}</th>
                        <th>{{ __('portal.permissions.current_role') }}</th>
                        <th>{{ __('portal.permissions.status') }}</th>
                        <th>{{ __('portal.team.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-center">
                                <div class="user-avatar-sm me-2">{{ strtoupper(substr($user->name, 0, 2)) }}</div>
                                <strong>{{ $user->name }}</strong>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                            <span class="role-badge-{{ $role->name }}">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="status-badge {{ $user->status->value === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($user->status->value) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="changeUserRole({{ $user->id }}, '{{ $user->name }}', '{{ $user->roles->first()?->name }}')">
                                <i class="fas fa-edit"></i> Change Role
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('portal.permissions.change_role_for') }} <span id="userName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('permissions.assign-role') }}">
                @csrf
                <input type="hidden" name="user_id" id="userId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.permissions.select_new_role') }} *</label>
                        <select name="role" class="form-control" id="roleSelect" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>{{ __('portal.permissions.role_descriptions') }}</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>{{ __('portal.permissions.client') }}:</strong> {{ __('portal.permissions.client_desc_short') }}</li>
                            <li><strong>{{ __('portal.permissions.employee') }}:</strong> {{ __('portal.permissions.employee_desc_short') }}</li>
                            <li><strong>{{ __('portal.permissions.manager') }}:</strong> {{ __('portal.permissions.manager_desc_short') }}</li>
                            <li><strong>{{ __('portal.permissions.project_manager') }}:</strong> {{ __('portal.permissions.project_manager_desc_short') }}</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ __('portal.permissions.update_role') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatar-sm {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-xs);
    font-weight: 600;
}

.role-badge-client { background: #3b82f6; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-employee { background: #0C7075; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-manager { background: #f59e0b; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-project_manager { background: #0C7075; color: white; padding: 4px 12px; border-radius: 4px; }

.status-badge {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: var(--font-size-xs);
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.success { background: #d1fae5; color: #065f46; }
.status-badge.warning { background: #fef3c7; color: #92400e; }
</style>
@endpush

@push('scripts')
<script>
function changeUserRole(userId, userName, currentRole) {
    document.getElementById('userId').value = userId;
    document.getElementById('userName').textContent = userName;
    document.getElementById('roleSelect').value = currentRole;
    new bootstrap.Modal(document.getElementById('changeRoleModal')).show();
}
</script>
@endpush
{{-- @endsection --}}

