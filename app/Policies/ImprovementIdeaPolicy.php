<?php

namespace App\Policies;

use App\Models\ImprovementIdea;
use App\Models\User;

/**
 * Submitters may view their own idea; managers may view and manage (approve /
 * reject) any idea. Auto-discovered by Laravel (App\Policies\{Model}Policy).
 */
class ImprovementIdeaPolicy
{
    public function view(User $user, ImprovementIdea $improvementIdea): bool
    {
        return $user->isManager() || $improvementIdea->user_id === $user->id;
    }

    public function manage(User $user, ImprovementIdea $improvementIdea): bool
    {
        return $user->isManager();
    }
}
