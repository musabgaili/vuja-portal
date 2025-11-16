<?php

namespace App\Http\Controllers\Permissions;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Permissions\PermissionsService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionsController extends Controller
{
    protected $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->middleware(['role:manager']);
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
    public function users()
    {
        $users = $this->permissionsService->getUsersWithRoles();
        $roles = Role::all();
        return view('permissions.users', compact('users', 'roles'));
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
