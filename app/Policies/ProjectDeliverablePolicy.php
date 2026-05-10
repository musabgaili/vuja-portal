<?php

namespace App\Policies;

use App\Models\ProjectDeliverable;
use App\Models\User;

class ProjectDeliverablePolicy
{
    public function view(User $user, ProjectDeliverable $projectDeliverable): bool
    {
        return $projectDeliverable->project->canUserView($user);
    }

    public function confirm(User $user, ProjectDeliverable $projectDeliverable): bool
    {
        return $user->canUseClientProjectPortal()
            && $projectDeliverable->project->client_id === $user->id;
    }

    public function delete(User $user, ProjectDeliverable $projectDeliverable): bool
    {
        return $user->isManager();
    }
}
