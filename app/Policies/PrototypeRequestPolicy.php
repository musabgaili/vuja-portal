<?php

namespace App\Policies;

use App\Models\PrototypeRequest;
use App\Models\User;

class PrototypeRequestPolicy
{
    public function view(User $user, PrototypeRequest $prototypeRequest): bool
    {
        return $user->isInternal() || $prototypeRequest->user_id === $user->id;
    }

    public function manage(User $user, PrototypeRequest $prototypeRequest): bool
    {
        return $user->isInternal();
    }
}
