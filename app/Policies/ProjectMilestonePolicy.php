<?php

namespace App\Policies;

use App\Models\ProjectMilestone;
use App\Models\User;

class ProjectMilestonePolicy
{
    public function view(User $user, ProjectMilestone $projectMilestone): bool
    {
        return $projectMilestone->project->canUserView($user);
    }

    public function update(User $user, ProjectMilestone $projectMilestone): bool
    {
        return $projectMilestone->project->canUserManageMilestones($user);
    }

    public function delete(User $user, ProjectMilestone $projectMilestone): bool
    {
        return $projectMilestone->project->canUserManageMilestones($user);
    }

    public function approve(User $user, ProjectMilestone $projectMilestone): bool
    {
        return $user->canUseClientProjectPortal()
            && $projectMilestone->project->client_id === $user->id;
    }
}
