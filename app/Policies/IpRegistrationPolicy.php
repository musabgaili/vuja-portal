<?php

namespace App\Policies;

use App\Models\IpRegistration;
use App\Models\User;

class IpRegistrationPolicy
{
    public function view(User $user, IpRegistration $ipRegistration): bool
    {
        return $user->isInternal() || $ipRegistration->user_id === $user->id;
    }

    public function update(User $user, IpRegistration $ipRegistration): bool
    {
        return $user->isInternal() || $ipRegistration->user_id === $user->id;
    }

    public function manage(User $user, IpRegistration $ipRegistration): bool
    {
        return $user->isInternal();
    }
}
