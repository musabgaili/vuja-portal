<?php

namespace App\Policies;

use App\Models\ChatChannel;
use App\Models\User;

class ChatChannelPolicy
{
    /** Only an internal member of the channel may view it. */
    public function view(User $user, ChatChannel $channel): bool
    {
        return $user->isInternal() && $channel->hasMember($user);
    }

    /** Posting (messages, reactions, read receipts) requires membership. */
    public function post(User $user, ChatChannel $channel): bool
    {
        return $this->view($user, $channel);
    }
}
