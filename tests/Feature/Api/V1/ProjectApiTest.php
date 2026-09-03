<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use App\Services\Projects\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
    }

    private function projectFor(User $member, ?User $client = null): Project
    {
        $client ??= User::factory()->create(['role' => UserRole::CLIENT, 'type' => 'client']);
        $project = Project::create([
            'client_id' => $client->id,
            'title' => 'API Test Project',
            'description' => 'Desc',
            'status' => 'in_progress',
        ]);
        app(ProjectService::class)->addProjectPerson($project, $member->id, 'employee', true);

        return $project;
    }

    public function test_internal_user_lists_projects(): void
    {
        $pm = User::factory()->create([
            'role' => UserRole::PROJECT_MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $this->projectFor($pm);

        Sanctum::actingAs($pm);

        $this->getJson('/api/v1/projects')
            ->assertOk()
            ->assertJsonPath('data.0.title', 'API Test Project')
            ->assertJsonStructure(['data', 'meta' => ['stats']]);
    }

    public function test_show_project_with_permissions(): void
    {
        $manager = $this->manager();
        $project = $this->projectFor($manager);

        Sanctum::actingAs($manager);

        $this->getJson('/api/v1/projects/'.$project->uuid)
            ->assertOk()
            ->assertJsonPath('project.title', 'API Test Project')
            ->assertJsonPath('permissions.can_manage_tasks', true)
            ->assertJsonStructure(['deep_link']);
    }

    public function test_create_milestone_and_task(): void
    {
        $manager = $this->manager();
        $project = $this->projectFor($manager);
        $project->update(['project_manager_id' => $manager->id]);

        Sanctum::actingAs($manager);

        $milestoneResponse = $this->postJson('/api/v1/projects/'.$project->uuid.'/milestones', [
            'title' => 'Phase 1',
            'due_date' => now()->addWeek()->toDateString(),
        ])->assertCreated();

        $milestoneId = $milestoneResponse->json('milestone.id');

        $this->postJson('/api/v1/projects/'.$project->uuid.'/tasks', [
            'title' => 'Wireframes',
            'priority' => 'high',
            'milestone_id' => $milestoneId,
            'assigned_to' => $manager->id,
        ])->assertCreated();

        $this->getJson('/api/v1/projects/'.$project->uuid.'/tasks/kanban')
            ->assertOk()
            ->assertJsonPath('columns.todo.0.title', 'Wireframes');
    }

    public function test_patch_milestone_status(): void
    {
        $manager = $this->manager();
        $project = $this->projectFor($manager);
        $project->update(['project_manager_id' => $manager->id]);

        $milestone = ProjectMilestone::create([
            'project_id' => $project->id,
            'title' => 'M1',
            'milestone_order' => 1,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($manager);

        $this->patchJson('/api/v1/milestones/'.$milestone->uuid, [
            'status' => 'in_progress',
        ])->assertOk()->assertJsonPath('milestone.status', 'in_progress');
    }
}
