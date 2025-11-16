@extends('layouts.internal-dashboard')

@section('title', 'Permissions Management')
@section('page-title', 'Roles & Permissions Management')

@section('content')
<!-- Quick Stats -->
<div class="dashboard-grid mb-4">
    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Total Roles</h3>
            <div class="widget-icon primary"><i class="fas fa-user-tag"></i></div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $roles->count() }}</span>
                    <span class="stat-label">Active Roles</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Total Permissions</h3>
            <div class="widget-icon success"><i class="fas fa-key"></i></div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $permissions->flatten()->count() }}</span>
                    <span class="stat-label">Permissions</span>
                </div>
            </div>
        </div>
    </div>

    <div class="widget">
        <div class="widget-header">
            <h3 class="widget-title">Total Users</h3>
            <div class="widget-icon info"><i class="fas fa-users"></i></div>
        </div>
        <div class="widget-content">
            <div class="widget-stats">
                <div class="stat-item">
                    <span class="stat-number">{{ $users->count() }}</span>
                    <span class="stat-label">System Users</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Links -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Quick Actions</h3>
    </div>
    <div class="card-content">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('permissions.roles') }}" class="btn btn-primary">
                <i class="fas fa-user-tag"></i> Manage Roles
            </a>
            <a href="{{ route('permissions.permissions') }}" class="btn btn-secondary">
                <i class="fas fa-key"></i> Manage Permissions
            </a>
            <a href="{{ route('permissions.users') }}" class="btn btn-secondary">
                <i class="fas fa-users"></i> Assign User Roles
            </a>
            <button class="btn btn-success" onclick="showCreatePermissionModal()">
                <i class="fas fa-plus"></i> Create Permission
            </button>
        </div>
    </div>
</div>

<!-- Roles & Their Permissions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Roles & Permissions Matrix</h3>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Users</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ ucfirst($role->name) }}</strong>
                            <br>
                            <small class="text-muted">{{ $role->users->count() }} users</small>
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
                            <span class="badge bg-primary">{{ $role->permissions->count() }} permissions</span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editRolePermissions({{ $role->id }}, '{{ $role->name }}')">
                                <i class="fas fa-edit"></i> Edit Permissions
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
        <h3 class="card-title">Permissions by Category</h3>
    </div>
    <div class="card-content">
        @foreach($permissions as $category => $perms)
        <div class="permission-category mb-4">
            <h5 class="category-title">
                <i class="fas fa-folder"></i> {{ ucfirst($category) }} Permissions
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
                <h5 class="modal-title">Edit Permissions for <span id="roleName"></span></h5>
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Permissions
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
                <h5 class="modal-title">Create New Permission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('permissions.create-permission') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Permission Name *</label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="e.g., view projects, edit tasks" required>
                        <small class="text-muted">Use format: "action resource" (e.g., view users, edit projects)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create Permission
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
    background: white;
    padding: var(--space-md);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.permission-name {
    font-weight: 600;
    color: var(--text-color);
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

function editRolePermissions(roleId, roleName) {
    document.getElementById('roleName').textContent = roleName;
    document.getElementById('editPermissionsForm').action = `/permissions/roles/${roleId}/update-permissions`;
    
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

