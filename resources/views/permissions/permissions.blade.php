@extends('layouts.internal-dashboard')

@section('title', 'Permissions Management')
@section('page-title', 'Manage Permissions')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Permissions List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">All Permissions</h3>
                <button class="btn btn-success btn-sm" onclick="showCreateModal()">
                    <i class="fas fa-plus"></i> Create Permission
                </button>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Permission Name</th>
                                <th>Assigned to Roles</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                            <tr>
                                <td>
                                    <strong>{{ $permission->name }}</strong>
                                </td>
                                <td>
                                    @foreach($roles as $role)
                                        @if($role->hasPermissionTo($permission))
                                        <span class="role-badge-{{ $role->name }}">{{ $role->name }}</span>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('permissions.delete-permission', $permission) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this permission?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-error">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Permission Guidelines -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Permission Guidelines</h3>
            </div>
            <div class="card-content">
                <div class="guideline-item">
                    <i class="fas fa-check text-success"></i>
                    <div>
                        <strong>Naming Convention</strong>
                        <p>Use format: "action resource"</p>
                        <code>view projects</code>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-check text-success"></i>
                    <div>
                        <strong>Actions</strong>
                        <p>view, create, edit, delete, manage</p>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-check text-success"></i>
                    <div>
                        <strong>Resources</strong>
                        <p>users, projects, tasks, clients, etc.</p>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <div>
                        <strong>Caution</strong>
                        <p>Deleting permissions affects all roles!</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Permissions Count -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Statistics</h3>
            </div>
            <div class="card-content">
                <div class="stat-row">
                    <strong>Total Permissions:</strong>
                    <span>{{ $permissions->count() }}</span>
                </div>
                <div class="stat-row">
                    <strong>Total Roles:</strong>
                    <span>{{ $roles->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Permission Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
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
                               placeholder="e.g., view projects, edit tasks, delete users" required>
                        <small class="text-muted">Use format: "action resource"</small>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Examples:</strong>
                        <ul class="mb-0">
                            <li>view projects</li>
                            <li>edit tasks</li>
                            <li>delete users</li>
                            <li>manage clients</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus"></i> Create
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.guideline-item {
    display: flex;
    gap: var(--space-sm);
    margin-bottom: var(--space-md);
    padding-bottom: var(--space-md);
    border-bottom: 1px solid var(--gray-200);
}

.guideline-item:last-child {
    border-bottom: none;
}

.guideline-item i {
    flex-shrink: 0;
    margin-top: 4px;
}

.guideline-item strong {
    display: block;
    margin-bottom: 4px;
}

.guideline-item p {
    margin: 0;
    color: var(--gray-600);
    font-size: var(--font-size-sm);
}

.guideline-item code {
    background: var(--bg-tertiary);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: var(--font-size-xs);
}

.stat-row {
    display: flex;
    justify-content: space-between;
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--gray-200);
}

.stat-row:last-child {
    border-bottom: none;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-lg);
}

.role-card {
    background: var(--card-bg);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
    border-left: 4px solid;
    box-shadow: var(--shadow-light);
}

.role-header h4 {
    margin: 0;
    color: var(--text-color);
}

.role-badge {
    background: var(--primary-color);
    color: white;
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
}

.role-stats {
    margin: var(--space-md) 0;
}

.role-stats .stat {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    margin-bottom: var(--space-xs);
    color: var(--gray-600);
}

.permission-list {
    margin: 0;
    padding-left: 20px;
}

.permission-list li {
    font-size: var(--font-size-sm);
    margin-bottom: 4px;
}
</style>
@endpush

@push('scripts')
<script>
function showCreateModal() {
    new bootstrap.Modal(document.getElementById('createModal')).show();
}

function editRolePermissions(roleId, roleName) {
    window.location.href = `{{ route('permissions.index') }}#role-${roleId}`;
}
</script>
@endpush
{{-- @endsection --}}

