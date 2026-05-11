<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Project;
use App\Models\ProjectPerson;
use App\Models\ProjectScopeChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Round2RemediationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_permissions_screen_uses_internal_update_permissions_route(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $manager->assignRole('manager');

        $clientRole = Role::findByName('client');

        $response = $this->actingAs($manager)->get(route('permissions.index'));

        $response->assertOk();
        $response->assertSee('internal\\/permissions\\/roles\\/__ROLE__\\/update-permissions', false);
    }

    public function test_project_manager_only_sees_assigned_projects_in_internal_list(): void
    {
        $projectManager = User::factory()->create([
            'role' => UserRole::PROJECT_MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $projectManager->assignRole('project_manager');

        $assignedProject = Project::create([
            'title' => 'Assigned Project',
            'description' => 'Visible to the assigned PM.',
            'status' => 'planning',
        ]);

        $unassignedProject = Project::create([
            'title' => 'Unassigned Project',
            'description' => 'Should not appear for this PM.',
            'status' => 'planning',
        ]);

        ProjectPerson::create([
            'project_id' => $assignedProject->id,
            'user_id' => $projectManager->id,
            'role' => 'project_manager',
            'can_edit' => true,
        ]);

        $response = $this->actingAs($projectManager)->get(route('projects.manager.index'));

        $response->assertOk();
        $response->assertSee('Assigned Project', false);
        $response->assertDontSee('Unassigned Project', false);
    }

    public function test_awarded_project_budget_cannot_be_changed_directly(): void
    {
        $projectManager = User::factory()->create([
            'role' => UserRole::PROJECT_MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $projectManager->assignRole('project_manager');

        $project = Project::create([
            'title' => 'Awarded Project',
            'description' => 'Commercial terms are locked.',
            'status' => 'awarded',
            'budget' => 1000,
            'project_manager_id' => $projectManager->id,
        ]);

        ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $projectManager->id,
            'role' => 'project_manager',
            'can_edit' => true,
        ]);

        $this->actingAs($projectManager)->from(route('projects.manager.show', $project))
            ->put(route('projects.update', $project), [
                'title' => 'Awarded Project',
                'description' => 'Commercial terms are locked.',
                'status' => 'awarded',
                'budget' => 1500,
            ])
            ->assertRedirect(route('projects.manager.show', $project))
            ->assertSessionHasErrors('error');

        $this->assertSame('1000.00', $project->fresh()->budget);
    }

    public function test_completed_project_cannot_receive_new_milestones(): void
    {
        $projectManager = User::factory()->create([
            'role' => UserRole::PROJECT_MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $projectManager->assignRole('project_manager');

        $project = Project::create([
            'title' => 'Completed Project',
            'description' => 'No more milestones allowed.',
            'status' => 'completed',
            'project_manager_id' => $projectManager->id,
        ]);

        ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $projectManager->id,
            'role' => 'project_manager',
            'can_edit' => true,
        ]);

        $this->actingAs($projectManager)->from(route('projects.manager.show', $project))
            ->post(route('projects.milestones.store', $project), [
                'title' => 'Blocked Milestone',
            ])
            ->assertRedirect(route('projects.manager.show', $project))
            ->assertSessionHasErrors('error');

        $this->assertDatabaseMissing('project_milestones', [
            'project_id' => $project->id,
            'title' => 'Blocked Milestone',
        ]);
    }

    public function test_project_close_is_blocked_when_scope_change_is_pending(): void
    {
        $projectManager = User::factory()->create([
            'role' => UserRole::PROJECT_MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $projectManager->assignRole('project_manager');

        $project = Project::create([
            'title' => 'Scope Change Project',
            'description' => 'Pending scope change should block closure.',
            'status' => 'in_progress',
            'project_manager_id' => $projectManager->id,
        ]);

        ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $projectManager->id,
            'role' => 'project_manager',
            'can_edit' => true,
        ]);

        ProjectScopeChange::create([
            'project_id' => $project->id,
            'requested_by' => $projectManager->id,
            'title' => 'Extra work',
            'description' => 'Pending review.',
            'status' => 'pending',
        ]);

        $this->actingAs($projectManager)->from(route('projects.manager.show', $project))
            ->post(route('projects.close', $project), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('projects.manager.show', $project))
            ->assertSessionHasErrors('error');

        $this->assertSame('in_progress', $project->fresh()->status);
    }

    public function test_manager_can_access_financial_reports(): void
    {
        $manager = User::factory()->create([
            'role' => UserRole::MANAGER,
            'type' => 'internal',
            'status' => UserStatus::ACTIVE,
        ]);
        $manager->assignRole('manager');

        Permission::findOrCreate('view all projects');

        $this->actingAs($manager)->get(route('reports.financial'))
            ->assertOk()
            ->assertSee('Financial Reports', false);
    }
}
