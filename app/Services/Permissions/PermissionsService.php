<?php

namespace App\Services\Permissions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsService
{
    /**
     * Assign a role to a user
     */
    public function assignRoleToUser(User $user, string $roleName): void
    {
        DB::transaction(function () use ($user, $roleName) {
            $user->syncRoles([$roleName]);
            $profile = $this->mapSpatieRoleToUserProfile($roleName);
            if ($profile !== null) {
                $user->forceFill($profile)->save();
            }
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapSpatieRoleToUserProfile(string $roleName): ?array
    {
        return match ($roleName) {
            'client' => ['role' => UserRole::CLIENT, 'type' => 'client'],
            'employee' => ['role' => UserRole::EMPLOYEE, 'type' => 'internal'],
            'manager' => ['role' => UserRole::MANAGER, 'type' => 'internal'],
            'project_manager' => ['role' => UserRole::PROJECT_MANAGER, 'type' => 'internal'],
            default => null,
        };
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
            ->groupBy(function ($permission) {
                return explode(' ', $permission->name)[0];
            });
    }

    /**
     * Get all users with their roles
     */
    public function getUsersWithRoles(bool $clientsOnly = false)
    {
        return User::with('roles')
            ->when($clientsOnly, fn ($query) => $query->where('role', UserRole::CLIENT))
            ->orderBy('name')
            ->paginate(10);
    }
}
