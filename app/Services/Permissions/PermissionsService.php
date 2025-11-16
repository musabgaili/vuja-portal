<?php

namespace App\Services\Permissions;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionsService
{
    /**
     * Assign a role to a user
     */
    public function assignRoleToUser(User $user, string $roleName): void
    {
        $user->syncRoles([$roleName]);
    }

    /**
     * Remove a role from a user
     */
    public function removeRoleFromUser(User $user, string $roleName): void
    {
        $user->removeRole($roleName);
    }

    /**
     * Assign a permission to a role
     */
    public function assignPermissionToRole(Role $role, string $permissionName): void
    {
        $role->givePermissionTo($permissionName);
    }

    /**
     * Remove a permission from a role
     */
    public function removePermissionFromRole(Role $role, string $permissionName): void
    {
        $role->revokePermissionTo($permissionName);
    }

    /**
     * Create a new permission
     */
    public function createPermission(string $name): Permission
    {
        return Permission::create(['name' => $name]);
    }

    /**
     * Delete a permission
     */
    public function deletePermission(Permission $permission): bool
    {
        return $permission->delete();
    }

    /**
     * Update all permissions for a role
     */
    public function updateRolePermissions(Role $role, array $permissionIds): void
    {
        $permissions = Permission::whereIn('id', $permissionIds)->pluck('name');
        $role->syncPermissions($permissions);
    }

    /**
     * Get all roles with their permissions
     */
    public function getAllRolesWithPermissions()
    {
        return Role::with('permissions')->get();
    }

    /**
     * Get all permissions grouped by category
     */
    public function getGroupedPermissions()
    {
        return Permission::all()
            ->sortBy('name')
            ->groupBy(function($permission) {
                return explode(' ', $permission->name)[0];
            });
    }

    /**
     * Get all users with their roles
     */
    public function getUsersWithRoles()
    {
        return User::with('roles')->paginate(10);
    }
}

