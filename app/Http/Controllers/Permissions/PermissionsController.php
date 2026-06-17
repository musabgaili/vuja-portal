<?php

namespace App\Http\Controllers\Permissions;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Permissions\PermissionsService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    protected $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->middleware(function (Request $request, Closure $next) {
            if (! $request->user()?->isManager()) {
                abort(403);
            }

            return $next($request);
        });
        $this->permissionsService = $permissionsService;
    }

    /**
     * Display the permissions management dashboard.
     */
    public function index()
    {
        $roles = $this->permissionsService->getAllRolesWithPermissions();
        $permissions = $this->permissionsService->getGroupedPermissions();
        $users = User::with('roles')->get();

        return view('permissions.index', compact('roles', 'permissions', 'users'));
    }

    /**
     * Show roles management page.
     */
    public function roles()
    {
        $roles = Role::withCount('users', 'permissions')->get();

        return view('permissions.roles', compact('roles'));
    }

    /**
     * Show permissions management page.
     */
    public function permissions()
    {
        $permissions = $this->permissionsService->getGroupedPermissions();

        return view('permissions.permissions', compact('permissions'));
    }

    /**
     * Show users management page.
     */
    public function users(Request $request)
    {
        $clientsOnly = $request->query('filter') === 'clients';
        $filters = [
            'verified' => $request->query('verified'),
            'q' => $request->query('q'),
        ];
        $users = $this->permissionsService->getUsersWithRoles($clientsOnly, $filters)->withQueryString();
        $roles = Role::all();
        $unverifiedClients = User::where('role', UserRole::CLIENT)->whereNull('email_verified_at')->count();

        return view('permissions.users', compact('users', 'roles', 'clientsOnly', 'filters', 'unverifiedClients'));
    }

    /**
     * Shortcut to the users list filtered to portal client accounts.
     */
    public function portalClients(): RedirectResponse
    {
        return redirect()->route('permissions.users', ['filter' => 'clients']);
    }

    /**
     * Delete a single account. Only CLIENT accounts are deletable — internal
     * staff and the current user are always protected.
     */
    public function destroyUser(User $user)
    {
        if (! $this->isDeletable($user)) {
            return back()->withErrors(['user' => __('portal.permissions.cannot_delete_user')]);
        }

        $ok = $this->safelyDeleteUser($user);

        return back()->with(
            $ok ? 'success' : 'error',
            $ok ? __('portal.permissions.user_deleted') : __('portal.permissions.user_delete_failed')
        );
    }

    /** Delete the checked accounts (clients only; protected accounts are skipped). */
    public function bulkDeleteUsers(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer',
        ]);

        $deleted = 0;
        $skipped = 0;
        User::whereIn('id', $validated['user_ids'])->get()->each(function (User $u) use (&$deleted, &$skipped) {
            ($this->isDeletable($u) && $this->safelyDeleteUser($u)) ? $deleted++ : $skipped++;
        });

        return back()->with('success', __('portal.permissions.bulk_deleted', ['deleted' => $deleted, 'skipped' => $skipped]));
    }

    /** One-click bot cleanup: delete every unverified client account. */
    public function deleteUnverifiedClients()
    {
        $deleted = 0;
        $skipped = 0;
        User::where('role', UserRole::CLIENT)->whereNull('email_verified_at')->get()
            ->each(function (User $u) use (&$deleted, &$skipped) {
                ($this->isDeletable($u) && $this->safelyDeleteUser($u)) ? $deleted++ : $skipped++;
            });

        return back()->with('success', __('portal.permissions.unverified_deleted', ['deleted' => $deleted, 'skipped' => $skipped]));
    }

    /** Guard: never the current user, and only client accounts (staff protected). */
    private function isDeletable(User $user): bool
    {
        return $user->id !== Auth::id() && $user->role === UserRole::CLIENT;
    }

    /** Detach roles then hard-delete; returns false if linked records block it. */
    private function safelyDeleteUser(User $user): bool
    {
        try {
            $user->roles()->detach();
            $user->delete();

            return true;
        } catch (\Throwable $e) {
            return false; // e.g. a foreign-key restriction from related records
        }
    }

    /**
     * Manually approve/activate a pending account. Sets status to ACTIVE and marks
     * the email verified so the user can sign in without the verification email —
     * useful when the mail never arrived or for staff created/promoted in-app.
     */
    public function activateUser(User $user): RedirectResponse
    {
        $user->forceFill([
            'status' => \App\Enums\UserStatus::ACTIVE,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return back()->with('success', __('portal.permissions.user_activated', ['name' => $user->name]));
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $this->permissionsService->assignRoleToUser($user, $validated['role']);

        return back()->with('success', 'Role assigned successfully!');
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $this->permissionsService->removeRoleFromUser($user, $validated['role']);

        return back()->with('success', 'Role removed successfully!');
    }

    /**
     * Assign a permission to a role.
     */
    public function assignPermissionToRole(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $this->permissionsService->assignPermissionToRole($role, $validated['permission']);

        return back()->with('success', 'Permission assigned to role successfully!');
    }

    /**
     * Remove a permission from a role.
     */
    public function removePermissionFromRole(Request $request)
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission' => 'required|exists:permissions,name',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $this->permissionsService->removePermissionFromRole($role, $validated['permission']);

        return back()->with('success', 'Permission removed from role successfully!');
    }

    /**
     * Create a new permission.
     */
    public function createPermission(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $this->permissionsService->createPermission($validated['name']);

        return back()->with('success', 'Permission created successfully!');
    }

    /**
     * Delete a permission.
     */
    public function deletePermission(Permission $permission)
    {
        $this->permissionsService->deletePermission($permission);

        return back()->with('success', 'Permission deleted successfully!');
    }

    /**
     * Update permissions for a role.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $this->permissionsService->updateRolePermissions($role, $validated['permissions'] ?? []);

        return back()->with('success', 'Role permissions updated successfully!');
    }
}
