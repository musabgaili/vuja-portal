<?php

namespace App\Services;

use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\ChatMessageMention;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Core operations for the internal team chat: sending/editing/deleting messages,
 * mention sync (which feeds the bell + Mentions inbox), reactions, channel/DM
 * resolution and per-user unread tracking. Access control lives in the policy /
 * controller; this service assumes the caller is authorized.
 */
class ChatService
{
    /** Send a message (+ optional attachments); bump the channel and mark the author read. */
    public function send(ChatChannel $channel, User $author, string $body, ?int $parentId = null, array $files = []): ChatMessage
    {
        return DB::transaction(function () use ($channel, $author, $body, $parentId, $files) {
            // A reply's parent must belong to THIS channel and itself be a root
            // message (one level of threading) — guards a forged cross-channel id.
            $parent = $parentId
                ? $channel->messages()->whereKey($parentId)->whereNull('parent_id')->first()
                : null;

            $message = $channel->messages()->create([
                'user_id' => $author->id,
                'parent_id' => $parent?->id,
                'body' => $body,
            ]);

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $path = $file->store('chat/'.$channel->id, 'public');
                    $message->attachments()->create([
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            $this->syncMentions($message, $this->parseMentions($channel, $body), $author);

            $channel->forceFill(['last_message_at' => now()])->save();
            $this->markRead($channel, $author);   // author has implicitly read their own message

            return $message;
        });
    }

    /** Edit a message body + re-resolve mentions (new tags become unread; removed ones drop). */
    public function edit(ChatMessage $message, string $body): ChatMessage
    {
        $message->forceFill(['body' => $body, 'edited_at' => now()])->save();
        $this->syncMentions($message, $this->parseMentions($message->channel, $body), $message->author);

        return $message;
    }

    /**
     * Resolve @mentions from the message body against the channel's members
     * (server-side = no trusting client ids; an edit re-derives automatically).
     * Longest names first so "@Ali Hassan" isn't shadowed by "@Ali".
     */
    public function parseMentions(ChatChannel $channel, string $body): array
    {
        if (! str_contains($body, '@')) {
            return [];
        }

        $members = $channel->members()->get(['users.id', 'users.name'])
            ->sortByDesc(fn ($u) => mb_strlen((string) $u->name));

        $ids = [];
        foreach ($members as $u) {
            if ($u->name && str_contains($body, '@'.$u->name)) {
                $ids[] = $u->id;
            }
        }

        return $ids;
    }

    public function delete(ChatMessage $message): void
    {
        $message->delete();   // soft delete — keeps thread structure intact
    }

    /**
     * Persist the @mentions for a message. Only channel members (minus the author)
     * can be mentioned, so a mention can never notify someone who can't see it.
     */
    public function syncMentions(ChatMessage $message, array $userIds, ?User $author = null): void
    {
        $authorId = $author?->id ?? $message->user_id;
        $memberIds = $message->channel->members()->pluck('users.id')->all();

        $valid = collect($userIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn ($id) => $id !== (int) $authorId && in_array($id, $memberIds, true))
            ->values();

        // Drop mentions no longer present (e.g. removed on edit).
        $message->mentions()->whereNotIn('user_id', $valid->all() ?: [0])->delete();

        foreach ($valid as $id) {
            ChatMessageMention::firstOrCreate(
                ['chat_message_id' => $message->id, 'user_id' => $id],
                ['read_at' => null],
            );
        }
    }

    /** Toggle one emoji reaction for a user on a message. */
    public function toggleReaction(ChatMessage $message, User $user, string $emoji): void
    {
        $existing = $message->reactions()->where('user_id', $user->id)->where('emoji', $emoji)->first();
        if ($existing) {
            $existing->delete();

            return;
        }
        $message->reactions()->create(['user_id' => $user->id, 'emoji' => $emoji]);
    }

    /** Advance the user's read pointer to the latest message + clear their mentions in this channel. */
    public function markRead(ChatChannel $channel, User $user): void
    {
        $lastId = (int) $channel->messages()->max('id');
        $channel->members()->updateExistingPivot($user->id, ['last_read_message_id' => $lastId]);

        ChatMessageMention::where('user_id', $user->id)
            ->whereNull('read_at')
            ->whereHas('message', fn ($q) => $q->where('chat_channel_id', $channel->id))
            ->update(['read_at' => now()]);
    }

    /** Create a team channel; the creator joins as admin. */
    public function createChannel(User $creator, string $name, ?string $description = null, array $memberIds = [], bool $isPrivate = false): ChatChannel
    {
        $channel = ChatChannel::create([
            'type' => 'channel',
            'name' => $name,
            'description' => $description,
            'is_private' => $isPrivate,
            'created_by' => $creator->id,
            'last_message_at' => now(),
        ]);

        $ids = collect($memberIds)->map(fn ($i) => (int) $i)->push($creator->id)->unique();
        $payload = [];
        foreach ($ids as $id) {
            $payload[$id] = ['role' => $id === $creator->id ? 'admin' : 'member', 'joined_at' => now()];
        }
        $channel->members()->attach($payload);

        return $channel;
    }

    /** Find (or create) the DM channel whose participants are EXACTLY {author} ∪ {others}. */
    public function resolveDirectChannel(User $author, array $otherUserIds): ChatChannel
    {
        $participantIds = collect($otherUserIds)
            ->map(fn ($i) => (int) $i)
            ->push($author->id)
            ->unique()
            ->sort()
            ->values();

        $existing = ChatChannel::where('type', 'dm')
            ->whereHas('members', fn ($q) => $q->whereKey($author->id))
            ->with('members:id')
            ->get()
            ->first(fn ($c) => $c->members->pluck('id')->sort()->values()->all() === $participantIds->all());

        if ($existing) {
            return $existing;
        }

        $channel = ChatChannel::create([
            'type' => 'dm',
            'is_private' => true,
            'created_by' => $author->id,
            'last_message_at' => now(),
        ]);
        $channel->members()->attach(
            $participantIds->mapWithKeys(fn ($id) => [$id => ['joined_at' => now()]])->all()
        );

        return $channel;
    }

    /** Per-channel unread counts + total unread + unread mention count for a user. */
    public function unreadCounts(User $user): array
    {
        $perChannel = [];
        $total = 0;

        foreach ($user->chatChannels()->get() as $c) {
            $lastRead = (int) ($c->pivot->last_read_message_id ?? 0);
            $count = $c->messages()->where('id', '>', $lastRead)->where('user_id', '!=', $user->id)->count();
            $perChannel[$c->id] = $count;
            $total += $count;
        }

        return [
            'channels' => $perChannel,
            'total' => $total,
            'mentions' => $user->unreadChatMentionsCount(),
        ];
    }
}
