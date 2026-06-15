@extends('layouts.internal-dashboard')

@section('title', __('portal.permissions.management_title'))
@section('page-title', __('portal.permissions.roles_and_permissions_management'))

@section('content')
<!-- Quick Stats -->
<div class="dashboard-grid mb-4">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.permissions.total_roles') }}</h3>
            <div class="widget-icon primary"><i class="fas fa-user-tag"></i></div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $roles->count() }}</span>
                    <span class="stat-label">{{ __('portal.permissions.active_roles') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.permissions.total_permissions') }}</h3>
            <div class="widget-icon success"><i class="fas fa-key"></i></div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $permissions->flatten()->count() }}</span>
                    <span class="stat-label">{{ __('portal.permissions.permissions') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">{{ __('portal.permissions.total_users') }}</h3>
            <div class="widget-icon info"><i class="fas fa-users"></i></div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $users->count() }}</span>
                    <span class="stat-label">{{ __('portal.permissions.system_users') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.internal.quick_actions') }}</h3>
    </div>
    <div class="card-content">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('permissions.roles') }}" class="btn btn-primary">
                <i class="fas fa-user-tag"></i> {{ __('portal.permissions.manage_roles') }}
            </a>
            <a href="{{ route('permissions.permissions') }}" class="btn btn-secondary">
                <i class="fas fa-key"></i> {{ __('portal.permissions.manage_permissions') }}
            </a>
            <a href="{{ route('permissions.users') }}" class="btn btn-secondary">
                <i class="fas fa-users"></i> {{ __('portal.permissions.assign_user_roles') }}
            </a>
            <button class="btn btn-primary" onclick="showCreatePermissionModal()">
                <i class="fas fa-plus"></i> {{ __('portal.permissions.create_permission') }}
            </button>
        </div>
    </div>
</div>

<!-- Roles & Their Permissions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.permissions.roles_permissions_matrix') }}</h3>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('portal.team.col_role') }}</th>
                        <th>{{ __('portal.permissions.users') }}</th>
                        <th>{{ __('portal.permissions.permissions') }}</th>
                        <th>{{ __('portal.team.col_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ ucfirst($role->name) }}</strong>
                            <br>
                            <small class="text-muted">{{ $role->users->count() }} {{ __('portal.permissions.users') }}</small>
                        </td>
                        <td>
                            <div class="user-avatars">
                                @foreach($role->users->take(5) as $user)
                                <span class="user-avatar-sm" title="{{ $user->name }}">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </span>
                                @endforeach
                                @if($role->users->count() > 5)
                                <span class="user-avatar-sm more">+{{ $role->users->count() - 5 }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $role->permissions->count() }} {{ __('portal.permissions.permissions') }}</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editRolePermissions({{ $role->id }}, '{{ $role->name }}')">
                                <i class="fas fa-edit"></i> {{ __('portal.permissions.edit_permissions') }}
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Permissions by Category -->
<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">{{ __('portal.permissions.permissions_by_category') }}</h3>
    </div>
    <div class="card-content">
        @foreach($permissions as $category => $perms)
        <div class="permission-category mb-4">
            <h5 class="category-title">
                <i class="fas fa-folder"></i> {{ ucfirst($category) }} {{ __('portal.permissions.permissions') }}
            </h5>
            <div class="permissions-grid">
                @foreach($perms as $permission)
                <div class="permission-item">
                    <div class="permission-name">{{ $permission->name }}</div>
                    <div class="permission-roles">
                        @foreach($roles as $role)
                            @if($role->hasPermissionTo($permission))
                            <span class="role-badge">{{ $role->name }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Edit Role Permissions Modal -->
<div class="modal fade" id="editPermissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('portal.permissions.edit_permissions_for') }} <span id="roleName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editPermissionsForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="permissions-checklist">
                        @foreach($permissions as $category => $perms)
                        <div class="permission-group">
                            <h6>{{ ucfirst($category) }}</h6>
                            @foreach($perms as $permission)
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox" type="checkbox" 
                                       name="permissions[]" value="{{ $permission->id }}" 
                                       id="perm-{{ $permission->id }}" data-role-id="">
                                <label class="form-check-label" for="perm-{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ __('portal.permissions.save_permissions') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div class="modal fade" id="createPermissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('portal.permissions.create_new_permission') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('permissions.create-permission') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.permissions.permission_name') }} *</label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="{{ __('portal.permissions.permission_name_placeholder') }}" required>
                        <small class="text-muted">{{ __('portal.permissions.permission_name_help') }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('portal.permissions.create_permission') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.user-avatars {
    display: flex;
    gap: var(--space-xs);
}

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

.user-avatar-sm.more {
    background: var(--gray-400);
}

.permission-category {
    padding: var(--space-lg);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
}

.category-title {
    color: var(--text-color);
    margin-bottom: var(--space-md);
    padding-bottom: var(--space-sm);
    border-bottom: 2px solid var(--primary-color);
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-md);
}

.permission-item {
    background: var(--card-bg, var(--bg-primary));
    padding: var(--space-md);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.permission-name {
    font-weight: 600;
    color: var(--text-color, var(--gray-900));
    margin-bottom: var(--space-xs);
}

.permission-roles {
    display: flex;
    gap: var(--space-xs);
    flex-wrap: wrap;
}

.role-badge {
    padding: 2px 8px;
    background: var(--success-color);
    color: white;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
}

.permission-group {
    margin-bottom: var(--space-lg);
    padding: var(--space-md);
    background: var(--bg-tertiary);
    border-radius: var(--radius-md);
}

.permission-group h6 {
    margin-bottom: var(--space-sm);
    color: var(--primary-color);
    font-weight: 600;
}

.permissions-checklist {
    max-height: 400px;
    overflow-y: auto;
}
</style>
@endpush

@push('scripts')
<script>
const rolesData = @json($roles);
const updatePermissionsRouteTemplate = @json(url('/internal/permissions/roles/__ROLE__/update-permissions'));

function editRolePermissions(roleId, roleName) {
    document.getElementById('roleName').textContent = roleName;
    document.getElementById('editPermissionsForm').action = updatePermissionsRouteTemplate.replace('__ROLE__', roleId);
    
    // Find role data
    const role = rolesData.find(r => r.id === roleId);
    
    // Reset all checkboxes
    document.querySelectorAll('.permission-checkbox').forEach(cb => {
        cb.checked = false;
        cb.dataset.roleId = roleId;
    });
    
    // Check permissions that role has
    if (role && role.permissions) {
        role.permissions.forEach(perm => {
            const checkbox = document.getElementById('perm-' + perm.id);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    new bootstrap.Modal(document.getElementById('editPermissionsModal')).show();
}

function showCreatePermissionModal() {
    new bootstrap.Modal(document.getElementById('createPermissionModal')).show();
}
</script>
@endpush
{{-- @endsection --}}

