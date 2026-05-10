<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->canUserView($user);
    }

    public function update(User $user, Project $project): bool
    {
        return $project->canUserEdit($user);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isManager();
    }

    public function manageTeam(User $user, Project $project): bool
    {
        return $project->canUserManageTeam($user);
    }

    public function manageMilestones(User $user, Project $project): bool
    {
        return $project->canUserManageMilestones($user);
    }

    public function manageTasks(User $user, Project $project): bool
    {
        return $project->canUserManageTasks($user);
    }

    public function manageExpenses(User $user, Project $project): bool
    {
        return $project->canUserManageExpenses($user);
    }

    public function manageScopeChanges(User $user, Project $project): bool
    {
        return $project->canUserManageScopeChanges($user);
    }
}
