<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientProjectsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_internal_employee_cannot_access_client_my_projects(): void
    {
        $employee = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($employee)->get(route('projects.client.index'))
            ->assertForbidden();
    }

    public function test_internal_project_manager_cannot_access_client_my_projects(): void
    {
        $pm = User::factory()->create([
            'role' => UserRole::PROJECT_MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($pm)->get(route('projects.client.index'))
            ->assertForbidden();
    }

    public function test_portal_client_can_access_my_projects(): void
    {
        $client = User::factory()->create([
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
        ]);

        $this->actingAs($client)->get(route('projects.client.index'))
            ->assertOk();
    }

    public function test_portal_client_sees_own_projects_in_list(): void
    {
        $client = User::factory()->create([
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
        ]);

        Project::create([
            'client_id' => $client->id,
            'title' => 'Alpha Client Project',
            'description' => 'Test description for the project.',
            'status' => 'active',
        ]);

        $this->actingAs($client)->get(route('projects.client.index'))
            ->assertOk()
            ->assertSee('Alpha Client Project', false);
    }

    public function test_portal_client_cannot_view_another_clients_project(): void
    {
        $client = User::factory()->create([
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
        ]);

        $other = User::factory()->create([
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
        ]);

        $project = Project::create([
            'client_id' => $other->id,
            'title' => 'Other Client Project',
            'description' => 'Owned by another user.',
            'status' => 'active',
        ]);

        $this->actingAs($client)->get(route('projects.client.show', $project))
            ->assertForbidden();
    }
}
