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

    /**
     * Add/remove members on a team channel (never a DM): the channel creator, a
     * channel admin, or a global manager who belongs to the channel.
     */
    public function manageMembers(User $user, ChatChannel $channel): bool
    {
        if ($channel->isDm() || ! $this->view($user, $channel)) {
            return false;
        }

        if ($channel->created_by === $user->id || $user->isManager()) {
            return true;
        }

        return $channel->members()->whereKey($user->id)->first()?->pivot->role === 'admin';
    }
}
