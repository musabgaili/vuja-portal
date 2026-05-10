<?php

namespace App\Policies;

use App\Models\CopyrightRegistration;
use App\Models\User;

class CopyrightRegistrationPolicy
{
    public function view(User $user, CopyrightRegistration $copyrightRegistration): bool
    {
        return $user->isInternal() || $copyrightRegistration->user_id === $user->id;
    }

    public function update(User $user, CopyrightRegistration $copyrightRegistration): bool
    {
        return $user->isInternal() || $copyrightRegistration->user_id === $user->id;
    }

    public function manage(User $user, CopyrightRegistration $copyrightRegistration): bool
    {
        return $user->isInternal();
    }
}
