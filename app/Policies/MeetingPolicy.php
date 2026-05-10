<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function view(User $user, Meeting $meeting): bool
    {
        return $meeting->client_id === $user->id
            || $meeting->team_member_id === $user->id
            || $user->isManager();
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return $this->view($user, $meeting);
    }
}
