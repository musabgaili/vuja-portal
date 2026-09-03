<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\StaffTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private function employee(): User
    {
        return User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
    }

    public function test_my_tasks_lists_staff_and_project_tasks(): void
    {
        $user = $this->employee();
        $other = $this->employee();

        StaffTask::factory()->create([
            'assigned_to' => $user->id,
            'assigned_by' => $other->id,
            'title' => 'Staff item',
            'status' => 'open',
        ]);

        $client = User::factory()->create(['role' => UserRole::CLIENT, 'type' => 'client']);
        $project = Project::create([
            'client_id' => $client->id,
            'title' => 'Alpha',
            'description' => 'Test project',
            'status' => 'active',
        ]);
        ProjectTask::create([
            'project_id' => $project->id,
            'assigned_to' => $user->id,
            'created_by' => $other->id,
            'title' => 'Project item',
            'status' => 'in_progress',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/my-tasks')
            ->assertOk()
            ->assertJsonCount(2, 'items')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment(['kind' => 'staff', 'title' => 'Staff item'])
            ->assertJsonFragment(['kind' => 'project', 'title' => 'Project item']);
    }

    public function test_employee_can_update_own_staff_task_status(): void
    {
        $user = $this->employee();
        $manager = User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);

        $task = StaffTask::factory()->create([
            'assigned_to' => $user->id,
            'assigned_by' => $manager->id,
            'status' => 'open',
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/staff-tasks/{$task->id}/status", ['status' => 'in_progress'])
            ->assertOk()
            ->assertJsonPath('task.status', 'in_progress');

        $this->assertDatabaseHas('staff_tasks', ['id' => $task->id, 'status' => 'in_progress']);
    }

    public function test_employee_cannot_update_another_users_staff_task(): void
    {
        $user = $this->employee();
        $other = $this->employee();

        $task = StaffTask::factory()->create([
            'assigned_to' => $other->id,
            'assigned_by' => $user->id,
            'status' => 'open',
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/staff-tasks/{$task->id}/status", ['status' => 'done'])
            ->assertForbidden();
    }
}
