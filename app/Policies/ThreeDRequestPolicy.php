<?php

namespace App\Policies;

use App\Models\ThreeDRequest;
use App\Models\User;

class ThreeDRequestPolicy
{
    public function view(User $user, ThreeDRequest $threeDRequest): bool
    {
        return $user->isInternal() || $threeDRequest->user_id === $user->id;
    }

    public function manage(User $user, ThreeDRequest $threeDRequest): bool
    {
        return $user->isInternal();
    }
}
