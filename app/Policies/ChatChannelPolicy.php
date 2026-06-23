<?php

namespace App\Policies;

use App\Models\ChatChannel;
use App\Models\User;

class ChatChannelPolicy
{
    /**
     * Members may view their channel. Managers may additionally open ANY team
     * channel (not DMs) for oversight, even ones they weren't added to.
     */
    public function view(User $user, ChatChannel $channel): bool
    {
        if (! $user->isInternal()) {
            return false;
        }

        if ($channel->hasMember($user)) {
            return true;
        }

        return $user->isManager() && ! $channel->isDm();
    }

    /** An internal non-member may ask to join a PUBLIC team channel. */
    public function requestToJoin(User $user, ChatChannel $channel): bool
    {
        return $user->isInternal()
            && ! $channel->isDm()
            && ! $channel->is_private
            && ! $channel->hasMember($user);
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
