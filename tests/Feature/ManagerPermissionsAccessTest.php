<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerPermissionsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_manager_can_access_permissions_index(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $manager->assignRole('manager');

        $this->actingAs($manager)->get(route('permissions.index'))
            ->assertOk();
    }

    public function test_manager_can_access_portal_clients_redirect_target(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $manager->assignRole('manager');

        $this->actingAs($manager)->get(route('permissions.users', ['filter' => 'clients']))
            ->assertOk();
    }

    public function test_employee_cannot_access_permissions_index(): void
    {
        $employee = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $employee->assignRole('employee');

        $this->actingAs($employee)->get(route('permissions.index'))
            ->assertForbidden();
    }
}
