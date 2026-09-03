<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\StaffTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_employee_gets_dashboard_summary(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
            'impact_points' => 42,
        ]);

        StaffTask::factory()->create([
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'status' => 'open',
            'title' => 'Review scope',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonPath('staff_tasks_open', 1)
            ->assertJsonPath('open_tasks', 1)
            ->assertJsonPath('impact_points', 42)
            ->assertJsonStructure([
                'open_tasks',
                'unread_notifications',
                'unread_chat_messages',
                'next_meeting',
            ]);
    }

    public function test_client_cannot_access_internal_dashboard(): void
    {
        $client = User::factory()->create([
            'role' => UserRole::CLIENT,
            'type' => 'client',
            'status' => UserStatus::ACTIVE,
        ]);

        Sanctum::actingAs($client);

        $this->getJson('/api/v1/dashboard')->assertForbidden();
    }

    public function test_manager_dashboard_includes_approval_stats(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/dashboard')
            ->assertOk()
            ->assertJsonStructure(['pending_approvals', 'stats' => ['team_count', 'active_projects']]);
    }
}
