<?php

namespace App\Policies;

use App\Models\ProjectScopeChange;
use App\Models\User;

class ProjectScopeChangePolicy
{
    public function view(User $user, ProjectScopeChange $projectScopeChange): bool
    {
        return $projectScopeChange->project->canUserView($user);
    }

    public function update(User $user, ProjectScopeChange $projectScopeChange): bool
    {
        return $projectScopeChange->project->canUserManageScopeChanges($user);
    }
}
