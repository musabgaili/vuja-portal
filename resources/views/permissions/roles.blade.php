@extends('layouts.internal-dashboard')

@section('title', 'Roles Management')
@section('page-title', 'Manage Roles')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">System Roles</h3>
        <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-content">
        <div class="roles-grid">
            @foreach($roles as $role)
            <div class="role-card" style="border-left-color: {{ $role->name === 'manager' ? '#f59e0b' : ($role->name === 'employee' ? '#10b981' : ($role->name === 'client' ? '#3b82f6' : '#8b5cf6')) }};">
                <div class="role-header">
                    <h4>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</h4>
                    <span class="role-badge">{{ $role->users_count }} users</span>
                </div>
                <div class="role-stats">
                    <div class="stat">
                        <i class="fas fa-key"></i>
                        <span>{{ $role->permissions_count }} permissions</span>
                    </div>
                    <div class="stat">
                        <i class="fas fa-users"></i>
                        <span>{{ $role->users_count }} users</span>
                    </div>
                </div>
                <div class="role-actions">
                    <button class="btn btn-primary btn-sm" onclick="editRolePermissions({{ $role->id }}, '{{ $role->name }}')">
                        <i class="fas fa-edit"></i> Edit Permissions
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
        <h3 class="card-title">Role Descriptions & Access Levels</h3>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Access Level</th>
                        <th>Key Permissions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="role-badge-manager">Manager</span></td>
                        <td>Full system access, can manage all users and requests</td>
                        <td><span class="badge bg-danger">Full Access</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>Manage all service requests</li>
                                <li>Assign tasks to employees</li>
                                <li>Send quotes & agreements</li>
                                <li>Verify payments</li>
                                <li>Manage permissions & roles</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="role-badge-project_manager">Project Manager</span></td>
                        <td>Manages assigned projects and teams</td>
                        <td><span class="badge bg-warning">Project Level</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>View assigned projects</li>
                                <li>Manage project tasks</li>
                                <li>Add/remove team members</li>
                                <li>View project expenses</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="role-badge-employee">Employee</span></td>
                        <td>Works on assigned tasks and consultations</td>
                        <td><span class="badge bg-success">Task Level</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>View assigned tasks</li>
                                <li>Update task status</li>
                                <li>Send meeting invitations</li>
                                <li>Mark tasks complete</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="role-badge-client">Client</span></td>
                        <td>Submits requests and tracks progress</td>
                        <td><span class="badge bg-info">Client Level</span></td>
                        <td>
                            <ul class="permission-list">
                                <li>Submit service requests</li>
                                <li>View own projects</li>
                                <li>Upload files & payments</li>
                                <li>Comment on projects</li>
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

