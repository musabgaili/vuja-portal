<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\ProjectPerson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_manager_can_create_when_the_same_person_is_pm_and_team_member(): void
    {
        $pm = $this->internal(UserRole::PROJECT_MANAGER);
        $client = $this->internal(UserRole::CLIENT, 'client');
        $colleague = $this->internal(UserRole::EMPLOYEE);

        $response = $this->actingAs($pm)->post(route('projects.store'), [
            'client_id' => $client->id,
            'title' => 'New build',
            'description' => 'Scope for the new build.',
            'project_manager_id' => $colleague->id,
            'team_members' => [$colleague->id],
        ]);

        $response->assertRedirect();
        $this->assertSame(1, ProjectPerson::where('user_id', $colleague->id)->count());

        $project = ProjectPerson::where('user_id', $colleague->id)->first()->project;
        $this->assertSame($pm->id, $project->created_by);
        $this->actingAs($pm)->get(route('projects.manager.show', $project))
            ->assertOk()
            ->assertSee($pm->name, false);
    }

    private function internal(UserRole $role, string $type = 'internal'): User
    {
        return User::factory()->create([
            'role' => $role,
            'type' => $type,
            'status' => UserStatus::ACTIVE,
        ]);
    }
}
