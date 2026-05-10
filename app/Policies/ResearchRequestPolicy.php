<?php

namespace App\Policies;

use App\Models\ResearchRequest;
use App\Models\User;

class ResearchRequestPolicy
{
    public function view(User $user, ResearchRequest $researchRequest): bool
    {
        return $user->isInternal() || $researchRequest->user_id === $user->id;
    }

    public function update(User $user, ResearchRequest $researchRequest): bool
    {
        return $user->isInternal() || $researchRequest->user_id === $user->id;
    }

    public function manage(User $user, ResearchRequest $researchRequest): bool
    {
        return $user->isInternal();
    }
}
