<?php

namespace App\Services;

use App\Models\ChatChannel;
use App\Models\ChatMessage;
use App\Models\ChatMessageMention;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
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
                    // PRIVATE disk + gated download route — chat files must not be
                    // world-readable via /storage (DM/private-channel confidentiality).
                    $path = $file->store('chat/'.$channel->id, 'private');
                    $message->attachments()->create([
                        'disk' => 'private',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),   // content-sniffed, not the client header
                        'size' => $file->getSize(),
                    ]);
                }
            }

            $this->syncMentions($message, $this->parseMentions($channel, $body), $author);

            // A reply bumps its parent so pollers refresh the parent's reply count.
            $parent?->touch();

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
     * Token-boundary match so "@Ali" does NOT match inside "@Alice".
     */
    public function parseMentions(ChatChannel $channel, string $body): array
    {
        if (! str_contains($body, '@')) {
            return [];
        }

        $ids = [];
        foreach ($channel->members()->get(['users.id', 'users.name']) as $u) {
            $name = (string) $u->name;
            if ($name === '') {
                continue;
            }
            // @Name preceded by a non-word/@ and followed by a non-word char.
            $pattern = '/(?<![\p{L}\p{N}_@])@'.preg_quote($name, '/').'(?![\p{L}\p{N}_])/u';
            if (preg_match($pattern, $body)) {
                $ids[] = $u->id;
            }
        }

        return $ids;
    }

    public function delete(ChatMessage $message): void
    {
        // Settle this message's unread mentions first, so the bell/Mentions badge
        // never counts a mention whose message has been (soft-)deleted.
        $message->mentions()->whereNull('read_at')->update(['read_at' => now()]);
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
        } else {
            $message->reactions()->create(['user_id' => $user->id, 'emoji' => $emoji]);
        }

        $message->touch();   // bump updated_at so pollers refresh reaction counts
    }

    /** Advance the user's read pointer to the latest message (does NOT clear mentions). */
    public function advanceRead(ChatChannel $channel, User $user): void
    {
        // Pointer tracks ROOT messages — the main stream only renders roots.
        $lastId = (int) $channel->messages()->whereNull('parent_id')->max('id');
        $channel->members()->updateExistingPivot($user->id, ['last_read_message_id' => $lastId]);
    }

    /**
     * Advance the read pointer AND clear this channel's @mentions for the user.
     * Called only when the user actively opens the conversation (show()) — NOT on
     * the background poll, so a mention is never silently consumed unread.
     */
    public function markRead(ChatChannel $channel, User $user): void
    {
        $this->advanceRead($channel, $user);

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

        // Lock on the participant signature so two simultaneous "start DM" requests
        // for the same people can't each create a duplicate DM channel.
        return Cache::lock('chat-dm:'.$participantIds->implode('-'), 5)->block(5, function () use ($author, $participantIds) {
            $existing = ChatChannel::where('type', 'dm')
                ->whereHas('members', fn ($q) => $q->whereKey($author->id))
                ->withCount('members')
                ->with('members:id')
                ->get()
                ->first(fn ($c) => $c->members_count === $participantIds->count()
                    && $c->members->pluck('id')->sort()->values()->all() === $participantIds->all());

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
        });
    }

    /**
     * Per-channel unread counts (ROOT messages only, matching the rendered stream)
     * + total + unread mention count. Single grouped query, not N+1.
     */
    public function unreadCounts(User $user): array
    {
        $rows = DB::table('chat_messages as m')
            ->join('chat_channel_user as p', 'p.chat_channel_id', '=', 'm.chat_channel_id')
            ->where('p.user_id', $user->id)
            ->whereNull('m.parent_id')
            ->whereNull('m.deleted_at')
            ->where('m.user_id', '!=', $user->id)
            ->whereColumn('m.id', '>', DB::raw('COALESCE(p.last_read_message_id, 0)'))
            ->groupBy('m.chat_channel_id')
            ->selectRaw('m.chat_channel_id as cid, COUNT(*) as c')
            ->pluck('c', 'cid');

        $perChannel = [];
        $total = 0;
        foreach ($rows as $cid => $c) {
            $perChannel[$cid] = (int) $c;
            $total += (int) $c;
        }

        return [
            'channels' => $perChannel,
            'total' => $total,
            'mentions' => $user->unreadChatMentionsCount(),
        ];
    }

    /**
     * Read-receipt state for a channel from the viewer's perspective: each OTHER
     * member's last_read_message_id + the total member count. The "Seen" indicator
     * on the viewer's own messages is derived from this.
     *
     * @return array{reads: array<int,int>, memberCount: int}
     */
    public function readState(ChatChannel $channel, int $viewerId): array
    {
        $members = $channel->members()->get(['users.id']);
        $reads = [];
        foreach ($members as $m) {
            if ($m->id !== $viewerId) {
                $reads[$m->id] = (int) ($m->pivot->last_read_message_id ?? 0);
            }
        }

        return ['reads' => $reads, 'memberCount' => $members->count()];
    }

    /**
     * Conversations (DMs + channels) with unread messages for a user — feeds the
     * notification bell. Each entry: channel_id, count, latest time, display title.
     */
    public function unreadConversations(User $user): array
    {
        $rows = DB::table('chat_messages as m')
            ->join('chat_channel_user as p', 'p.chat_channel_id', '=', 'm.chat_channel_id')
            ->where('p.user_id', $user->id)
            ->whereNull('m.parent_id')
            ->whereNull('m.deleted_at')
            ->where('m.user_id', '!=', $user->id)
            ->whereColumn('m.id', '>', DB::raw('COALESCE(p.last_read_message_id, 0)'))
            ->groupBy('m.chat_channel_id')
            ->selectRaw('m.chat_channel_id as cid, COUNT(*) as c, MAX(m.created_at) as at')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $channels = ChatChannel::whereIn('id', $rows->pluck('cid'))->with('members:id,name')->get()->keyBy('id');

        $out = [];
        foreach ($rows as $r) {
            $channel = $channels->get($r->cid);
            if (! $channel) {
                continue;
            }
            $out[] = [
                'channel_id' => (int) $r->cid,
                'count' => (int) $r->c,
                'at' => $r->at,
                'title' => $channel->displayName($user),
                'is_dm' => $channel->isDm(),
            ];
        }

        return $out;
    }
}
