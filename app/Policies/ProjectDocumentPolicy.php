<?php

namespace App\Policies;

use App\Models\ProjectDocument;
use App\Models\User;

class ProjectDocumentPolicy
{
    public function view(User $user, ProjectDocument $projectDocument): bool
    {
        return $projectDocument->project->canUserView($user);
    }

    public function update(User $user, ProjectDocument $projectDocument): bool
    {
        return $projectDocument->project->canUserEdit($user)
            || $projectDocument->uploaded_by === $user->id
            || $user->isManager();
    }

    public function delete(User $user, ProjectDocument $projectDocument): bool
    {
        return $projectDocument->project->canUserEdit($user)
            || $projectDocument->uploaded_by === $user->id
            || $user->isManager();
    }
}
