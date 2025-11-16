<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\ProjectPerson;

class CreateProjectAction
{
    public function execute(array $data): Project
    {
        $teamMembers = $data['team_members'] ?? [];
        $projectManagerId = $data['project_manager_id'] ?? null;
        
        unset($data['team_members'], $data['project_manager_id']);

        $project = Project::create($data);

        // Add client
        ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $data['client_id'],
            'role' => 'client',
            'can_edit' => false,
        ]);

        // Add project manager
        if ($projectManagerId) {
            ProjectPerson::create([
                'project_id' => $project->id,
                'user_id' => $projectManagerId,
                'role' => 'project_manager',
                'can_edit' => true,
            ]);
            $project->update(['project_manager_id' => $projectManagerId]);
        }

        // Add team members
        foreach ($teamMembers as $memberId) {
            ProjectPerson::create([
                'project_id' => $project->id,
                'user_id' => $memberId,
                'role' => 'employee',
                'can_edit' => false,
            ]);
        }

        return $project->fresh(['projectPeople.user']);
    }
}

