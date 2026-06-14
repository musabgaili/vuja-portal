@extends('layouts.internal-dashboard')

@section('title', __('portal.permissions.management_title'))
@section('page-title', __('portal.permissions.manage_permissions_title'))

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Permissions List -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.permissions.all_permissions') }}</h3>
                <button class="btn btn-primary btn-sm" onclick="showCreateModal()">
                    <i class="fas fa-plus"></i> {{ __('portal.permissions.create_permission') }}
                </button>
            </div>
            <div class="card-content">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('portal.permissions.permission_name') }}</th>
                                <th>{{ __('portal.permissions.assigned_to_roles') }}</th>
                                <th>{{ __('portal.team.col_actions') }}</th>
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
                                          onsubmit="return confirm('{{ __('portal.permissions.confirm_delete_permission') }}')" class="d-inline">
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
                <h3 class="card-title">{{ __('portal.permissions.permission_guidelines') }}</h3>
            </div>
            <div class="card-content">
                <div class="guideline-item">
                    <i class="fas fa-check text-success"></i>
                    <div>
                        <strong>{{ __('portal.permissions.naming_convention') }}</strong>
                        <p>{{ __('portal.permissions.use_format_action_resource') }}</p>
                        <code>view projects</code>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-check text-success"></i>
                    <div>
                        <strong>{{ __('portal.permissions.actions') }}</strong>
                        <p>{{ __('portal.permissions.actions_list') }}</p>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-check text-success"></i>
                    <div>
                        <strong>{{ __('portal.permissions.resources') }}</strong>
                        <p>{{ __('portal.permissions.resources_list') }}</p>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    <div>
                        <strong>{{ __('portal.permissions.caution') }}</strong>
                        <p>{{ __('portal.permissions.deleting_permissions_affects_all_roles') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Permissions Count -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">{{ __('portal.permissions.statistics') }}</h3>
            </div>
            <div class="card-content">
                <div class="stat-row">
                    <strong>{{ __('portal.permissions.total_permissions') }}:</strong>
                    <span>{{ $permissions->count() }}</span>
                </div>
                <div class="stat-row">
                    <strong>{{ __('portal.permissions.total_roles') }}:</strong>
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
                <h5 class="modal-title">{{ __('portal.permissions.create_new_permission') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('permissions.create-permission') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">{{ __('portal.permissions.permission_name') }} *</label>
                        <input type="text" name="name" class="form-control" 
                               placeholder="{{ __('portal.permissions.permission_name_placeholder_full') }}" required>
                        <small class="text-muted">{{ __('portal.permissions.use_format_action_resource') }}</small>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>{{ __('portal.permissions.examples') }}</strong>
                        <ul class="mb-0">
                            <li>view projects</li>
                            <li>edit tasks</li>
                            <li>delete users</li>
                            <li>manage clients</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('portal.team.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('portal.permissions.create') }}
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

