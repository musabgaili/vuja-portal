<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\ProjectPerson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateProjectAction
{
    public function execute(array $data): Project
    {
        $teamMembers = array_map('intval', $data['team_members'] ?? []);
        $projectManagerId = isset($data['project_manager_id']) ? (int) $data['project_manager_id'] : null;

        unset($data['team_members'], $data['project_manager_id']);

        return DB::transaction(function () use ($data, $teamMembers, $projectManagerId) {
            $project = Project::create($data);
            $placed = [];

            $this->place($project, (int) $data['client_id'], 'client', false);
            $placed[(int) $data['client_id']] = true;

            if ($projectManagerId) {
                $this->place($project, $projectManagerId, 'project_manager', true);
                $placed[$projectManagerId] = true;
                $project->update(['project_manager_id' => $projectManagerId]);
            }

            foreach (array_unique($teamMembers) as $memberId) {
                if (isset($placed[$memberId])) {
                    continue;
                }
                $this->place($project, $memberId, 'employee', false);
                $placed[$memberId] = true;
            }

            // The person who submitted the form must be able to open the project
            // they just created. A global project manager is not a site manager,
            // so the show page 403s unless they are on the team.
            $creatorId = (int) Auth::id();
            if ($creatorId && ! isset($placed[$creatorId])) {
                $this->place($project, $creatorId, 'employee', true);
            }

            return $project->fresh(['projectPeople.user']);
        });
    }

    private function place(Project $project, int $userId, string $role, bool $canEdit): void
    {
        ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $userId,
            'role' => $role,
            'can_edit' => $canEdit,
        ]);
    }
}
