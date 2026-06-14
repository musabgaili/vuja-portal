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
            $this->applySpatieMappedProfileToUser($user, $this->mapSpatieRoleToUserProfile($roleName));
        });
    }

    /**
     * @return array<string, mixed>|null role and type keys, or null if unmapped Spatie role name
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
     * Apply mapped profile to users.role / users.type when the Spatie role is known (no-op if unmapped).
     */
    private function applySpatieMappedProfileToUser(User $user, ?array $profile): void
    {
        if ($profile === null) {
            return;
        }

        $user->forceFill($profile)->save();
    }

    /**
     * When no Spatie role maps to our enum, fall back to portal client (safe default after role removal).
     */
    private function setUserToDefaultClient(User $user): void
    {
        $user->forceFill([
            'role' => UserRole::CLIENT,
            'type' => 'client',
        ])->save();
    }

    /**
     * After removing a Spatie role, align users.role / type with remaining roles or default to client.
     */
    private function syncUserProfileFromRemainingSpatieRoles(User $user): void
    {
        $user->load('roles');
        $remaining = $user->roles->first();

        if ($remaining === null) {
            $this->setUserToDefaultClient($user);

            return;
        }

        $profile = $this->mapSpatieRoleToUserProfile($remaining->name);
        if ($profile !== null) {
            $user->forceFill($profile)->save();
        } else {
            $this->setUserToDefaultClient($user);
        }
    }

    /**
     * Remove a role from a user
     */
    public function removeRoleFromUser(User $user, string $roleName): void
    {
        DB::transaction(function () use ($user, $roleName) {
            $user->removeRole($roleName);
            $this->syncUserProfileFromRemainingSpatieRoles($user);
        });
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
    public function getUsersWithRoles(bool $clientsOnly = false, array $filters = [])
    {
        $verified = $filters['verified'] ?? null;
        $search = trim((string) ($filters['q'] ?? ''));

        return User::with('roles')
            ->when($clientsOnly, fn ($query) => $query->where('role', UserRole::CLIENT))
            ->when($verified === 'unverified', fn ($q) => $q->whereNull('email_verified_at'))
            ->when($verified === 'verified', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            // Newest first — bot sign-ups cluster at the top for easy cleanup.
            ->orderByDesc('created_at')
            ->paginate(25);
    }
}
