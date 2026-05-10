<?php

namespace App\Policies;

use App\Models\IdeaRequest;
use App\Models\User;

class IdeaRequestPolicy
{
    public function view(User $user, IdeaRequest $ideaRequest): bool
    {
        return $user->isInternal() || $ideaRequest->user_id === $user->id;
    }

    public function update(User $user, IdeaRequest $ideaRequest): bool
    {
        return $user->isInternal() || $ideaRequest->user_id === $user->id;
    }

    public function manage(User $user, IdeaRequest $ideaRequest): bool
    {
        return $user->isInternal();
    }
}
