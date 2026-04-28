@extends('layouts.internal-dashboard')

@section('title', __('portal.permissions.roles_management_title'))
@section('page-title', __('portal.permissions.manage_roles_title'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.permissions.system_roles') }}</h3>
        <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> {{ __('portal.permissions.back') }}
        </a>
    </div>
    <div class="card-content">
        <div class="roles-grid">
            @foreach($roles as $role)
            <div class="role-card" style="border-left-color: {{ $role->name === 'manager' ? '#f59e0b' : ($role->name === 'employee' ? '#10b981' : ($role->name === 'client' ? '#3b82f6' : '#8b5cf6')) }};">
                <div class="role-header">
                    <h4>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</h4>
                    <span class="role-badge">{{ $role->users_count }} {{ __('portal.permissions.users') }}</span>
                </div>
                <div class="role-stats">
                    <div class="stat">
                        <i class="fas fa-key"></i>
                        <span>{{ $role->permissions_count }} {{ __('portal.permissions.permissions') }}</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-users"></i>
                        <span>{{ $role->users_count }} {{ __('portal.permissions.users') }}</span>
                    </div>
                </div>
                <div class="role-actions">
                    <button class="btn btn-primary btn-sm" onclick="editRolePermissions({{ $role->id }}, '{{ $role->name }}')">
                        <i class="fas fa-edit"></i> {{ __('portal.permissions.edit_permissions') }}
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Role Descriptions -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.permissions.role_descriptions_access_levels') }}</h3>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('portal.team.col_role') }}</th>
                        <th>{{ __('portal.permissions.description') }}</th>
                        <th>{{ __('portal.permissions.access_level') }}</th>
                        <th>{{ __('portal.permissions.key_permissions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="role-badge-manager">Manager</span></td>
                        <td>{{ __('portal.permissions.manager_desc') }}</td>
                        <td><span class="badge bg-danger">{{ __('portal.permissions.full_access') }}</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>{{ __('portal.permissions.manager_perm_manage_all_requests') }}</li>
                                <li>{{ __('portal.permissions.manager_perm_assign_tasks') }}</li>
                                <li>{{ __('portal.permissions.manager_perm_send_quotes') }}</li>
                                <li>{{ __('portal.permissions.manager_perm_verify_payments') }}</li>
                                <li>{{ __('portal.permissions.manager_perm_manage_permissions_roles') }}</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="role-badge-project_manager">Project Manager</span></td>
                        <td>{{ __('portal.permissions.project_manager_desc') }}</td>
                        <td><span class="badge bg-warning">{{ __('portal.permissions.project_level') }}</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>{{ __('portal.permissions.pm_perm_view_assigned_projects') }}</li>
                                <li>{{ __('portal.permissions.pm_perm_manage_project_tasks') }}</li>
                                <li>{{ __('portal.permissions.pm_perm_manage_team_members') }}</li>
                                <li>{{ __('portal.permissions.pm_perm_view_project_expenses') }}</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="role-badge-employee">Employee</span></td>
                        <td>{{ __('portal.permissions.employee_desc') }}</td>
                        <td><span class="badge bg-success">{{ __('portal.permissions.task_level') }}</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>{{ __('portal.permissions.employee_perm_view_assigned_tasks') }}</li>
                                <li>{{ __('portal.permissions.employee_perm_update_task_status') }}</li>
                                <li>{{ __('portal.permissions.employee_perm_send_meeting_invites') }}</li>
                                <li>{{ __('portal.permissions.employee_perm_mark_tasks_complete') }}</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="role-badge-client">Client</span></td>
                        <td>{{ __('portal.permissions.client_desc') }}</td>
                        <td><span class="badge bg-info">{{ __('portal.permissions.client_level') }}</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>{{ __('portal.permissions.client_perm_submit_requests') }}</li>
                                <li>{{ __('portal.permissions.client_perm_view_own_projects') }}</li>
                                <li>{{ __('portal.permissions.client_perm_upload_files_payments') }}</li>
                                <li>{{ __('portal.permissions.client_perm_comment_on_projects') }}</li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-lg);
}

.role-card {
    background: var(--card-bg);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
    border-left: 4px solid;
    box-shadow: var(--shadow-light);
}

.role-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-md);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--gray-200);
}

.role-header h4 {
    margin: 0;
    color: var(--text-color);
}

.role-badge {
    background: var(--primary-color);
    color: white;
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
}

.role-stats {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    margin-bottom: var(--space-md);
}

.role-stats .stat {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    color: var(--gray-600);
}

.role-stats i {
    color: var(--primary-color);
}

.role-actions {
    margin-top: var(--space-md);
}

.role-badge-client { background: #3b82f6; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
.role-badge-employee { background: #10b981; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
.role-badge-manager { background: #f59e0b; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
.role-badge-project_manager { background: #8b5cf6; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }

.permission-list {
    margin: 0;
    padding-left: 20px;
}

.permission-list li {
    font-size: var(--font-size-sm);
    color: var(--gray-600);
    margin-bottom: 4px;
}
</style>
@endpush

@push('scripts')
<script>
function editRolePermissions(roleId, roleName) {
    window.location.href = `/permissions?role=${roleId}`;
}
</script>
@endpush
{{-- @endsection --}}

