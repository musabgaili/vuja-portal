@extends('layouts.internal-dashboard')

@section('title', 'User Roles')
@section('page-title', 'Assign User Roles & Permissions')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">User Role Management</h3>
        <a href="{{ route('permissions.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Current Role</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                <h5 class="modal-title">Change Role for <span id="userName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('permissions.assign-role') }}">
                @csrf
                <input type="hidden" name="user_id" id="userId">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Select New Role *</label>
                        <select name="role_name" class="form-control" id="roleSelect" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Role Descriptions:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Client:</strong> Can submit requests, view their own data</li>
                            <li><strong>Employee:</strong> Can view assigned tasks, send meeting invites</li>
                            <li><strong>Manager:</strong> Full access to manage all requests and users</li>
                            <li><strong>Project Manager:</strong> Can manage assigned projects and teams</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Role
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
.role-badge-employee { background: #10b981; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-manager { background: #f59e0b; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-project_manager { background: #8b5cf6; color: white; padding: 4px 12px; border-radius: 4px; }

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

