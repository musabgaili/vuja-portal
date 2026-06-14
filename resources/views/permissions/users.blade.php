@extends('layouts.internal-dashboard')

@section('title', __('portal.permissions.user_roles_title'))
@section('page-title', __('portal.permissions.assign_user_roles_permissions'))

@php
    $myId = auth()->id();
    $deletable = fn($u) => $u->role->value === 'client' && $u->id !== $myId;
@endphp

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@foreach($errors->all() as $e)<div class="alert alert-danger">{{ $e }}</div>@endforeach

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
        {{-- Filters + bot cleanup --}}
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
            <form method="GET" class="d-flex flex-wrap align-items-end gap-2">
                @if(!empty($clientsOnly ?? false))<input type="hidden" name="filter" value="clients">@endif
                <div>
                    <label class="form-label mb-1" style="font-size:.8rem;">{{ __('portal.permissions.filter_search') }}</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="{{ __('portal.permissions.filter_search_ph') }}">
                </div>
                <div>
                    <label class="form-label mb-1" style="font-size:.8rem;">{{ __('portal.permissions.verification') }}</label>
                    <select name="verified" class="form-select form-select-sm">
                        <option value="">{{ __('portal.permissions.all') }}</option>
                        <option value="unverified" @selected(($filters['verified'] ?? '') === 'unverified')>{{ __('portal.permissions.unverified') }}</option>
                        <option value="verified" @selected(($filters['verified'] ?? '') === 'verified')>{{ __('portal.permissions.verified') }}</option>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> {{ __('portal.permissions.apply') }}</button>
            </form>

            @if(($unverifiedClients ?? 0) > 0)
                <form method="POST" action="{{ route('permissions.users.delete-unverified') }}"
                      onsubmit="return confirm('{{ __('portal.permissions.unverified_confirm') }}');">
                    @csrf
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-broom"></i> {{ __('portal.permissions.delete_all_unverified', ['count' => $unverifiedClients]) }}
                    </button>
                </form>
            @endif
        </div>

        <form method="POST" action="{{ route('permissions.users.bulk-delete') }}" id="bulkForm"
              onsubmit="return confirmBulk();">
            @csrf
            <div class="d-flex align-items-center gap-2 mb-2">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> {{ __('portal.permissions.delete_selected') }}</button>
                <span class="text-muted" style="font-size:.8rem;"><span id="selCount">0</span> {{ __('portal.permissions.selected') }}</span>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" id="checkAll" title="{{ __('portal.permissions.select_all') }}"></th>
                            <th>{{ __('portal.permissions.user') }}</th>
                            <th>{{ __('portal.email_address') }}</th>
                            <th>{{ __('portal.permissions.current_role') }}</th>
                            <th>{{ __('portal.permissions.verification') }}</th>
                            <th>{{ __('portal.permissions.registered') }}</th>
                            <th>{{ __('portal.team.col_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                @if($deletable($user))
                                    <input type="checkbox" class="row-check" name="user_ids[]" value="{{ $user->id }}">
                                @else
                                    <i class="fas fa-lock text-muted" title="{{ __('portal.permissions.protected') }}"></i>
                                @endif
                            </td>
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
                                @if($user->roles->isEmpty())<span class="role-badge-{{ $user->role->value }}">{{ ucfirst($user->role->value) }}</span>@endif
                            </td>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="status-badge success">{{ __('portal.permissions.verified') }}</span>
                                @else
                                    <span class="status-badge warning">{{ __('portal.permissions.unverified') }}</span>
                                @endif
                            </td>
                            <td><span class="text-muted" style="font-size:.82rem;">{{ $user->created_at?->format('M j, Y') }}</span></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="changeUserRole({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->roles->first()?->name ?? $user->role->value }}')">
                                        <i class="fas fa-user-tag"></i>
                                    </button>
                                    @if($deletable($user))
                                        <button type="submit" form="del-{{ $user->id }}" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('{{ __('portal.permissions.delete_user_confirm') }}');" title="{{ __('portal.permissions.delete_user') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('portal.permissions.no_users') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{-- Hidden per-row delete forms (kept outside the bulk form to avoid nesting) --}}
        @foreach($users as $user)
            @if($deletable($user))
                <form id="del-{{ $user->id }}" method="POST" action="{{ route('permissions.users.destroy', $user) }}" class="d-none">@csrf @method('DELETE')</form>
            @endif
        @endforeach

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
.user-avatar-sm { width: 30px; height: 30px; border-radius: 50%; background: var(--primary-color); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-xs); font-weight: 600; }
.role-badge-client { background: #3b82f6; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-employee { background: #0C7075; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-manager { background: #f59e0b; color: white; padding: 4px 12px; border-radius: 4px; }
.role-badge-project_manager { background: #0C7075; color: white; padding: 4px 12px; border-radius: 4px; }
.status-badge { padding: 4px 12px; border-radius: 4px; font-size: var(--font-size-xs); font-weight: 600; text-transform: uppercase; }
.status-badge.success { background: #d1fae5; color: #065f46; }
.status-badge.warning { background: #fef3c7; color: #92400e; }
</style>
@endpush

@push('scripts')
<script>
function changeUserRole(userId, userName, currentRole) {
    document.getElementById('userId').value = userId;
    document.getElementById('userName').textContent = userName;
    if (currentRole) document.getElementById('roleSelect').value = currentRole;
    new bootstrap.Modal(document.getElementById('changeRoleModal')).show();
}
(function () {
    var checkAll = document.getElementById('checkAll');
    var counter = document.getElementById('selCount');
    function rows() { return Array.prototype.slice.call(document.querySelectorAll('.row-check')); }
    function refresh() { counter.textContent = rows().filter(function (c) { return c.checked; }).length; }
    if (checkAll) checkAll.addEventListener('change', function () { rows().forEach(function (c) { c.checked = checkAll.checked; }); refresh(); });
    rows().forEach(function (c) { c.addEventListener('change', refresh); });
    window.confirmBulk = function () {
        var n = rows().filter(function (c) { return c.checked; }).length;
        if (n === 0) { alert('{{ __('portal.permissions.none_selected') }}'); return false; }
        return confirm('{{ __('portal.permissions.bulk_delete_confirm') }}'.replace(':count', n));
    };
    refresh();
})();
</script>
@endpush
