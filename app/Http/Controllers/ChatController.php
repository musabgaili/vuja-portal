<?php

namespace App\Http\Controllers;

use App\Models\ChatChannel;
use App\Models\ChatChannelJoinRequest;
use App\Models\ChatMessage;
use App\Models\ChatMessageMention;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ChatController extends Controller
{
    /** Allowed reaction emojis. */
    public const EMOJIS = ['👍', '✅', '🎉', '❤️', '👀', '🙏', '🔥', '😄'];

    /** How many root messages per page (initial load + "load earlier"). */
    public const PAGE_SIZE = 50;

    public function __construct(private ChatService $chat) {}

    // ===================================================================
    // Pages
    // ===================================================================

    /** Chat home → open the most recent conversation, or an empty state. */
    public function index()
    {
        $first = ChatChannel::forUser(Auth::user())->first();

        if ($first) {
            return redirect()->route('chat.show', $first);
        }

        return view('chat.index', [
            'channels' => collect(),
            'channel' => null,
            'messages' => collect(),
            'members' => collect(),
            'allUsers' => $this->internalUsers(),
        ]);
    }

    /** A channel / DM conversation view. */
    public function show(ChatChannel $channel)
    {
        $this->authorize('view', $channel);
        $user = Auth::user();

        $channels = ChatChannel::forUser($user)->with('members:id,name')->get();

        // Load only the most recent page; older messages load on demand (chat.older).
        $messages = $channel->messages()->whereNull('parent_id')
            ->with($this->messageEagerLoads())
            ->withCount('replies')
            ->orderByDesc('id')
            ->limit(self::PAGE_SIZE)
            ->get()
            ->reverse()
            ->values();

        // Members of THIS channel power the @-mention autocomplete (mentionable = members only).
        $members = $channel->members()->select('users.id', 'users.name')->orderBy('name')->get();

        $this->chat->markRead($channel, $user);

        $allUsers = $this->internalUsers();
        $readState = $this->chat->readState($channel, $user->id);   // for "Seen" receipts

        // Pending join requests (for the manage-members modal + header badge).
        $pendingRequests = $channel->isDm()
            ? collect()
            : $channel->pendingJoinRequests()->with('user:id,name')->get();

        return view('chat.index', compact('channels', 'channel', 'messages', 'members', 'allUsers', 'readState', 'pendingRequests'));
    }

    /** My Mentions inbox — every message that tagged me, unread first. */
    public function mentions()
    {
        $user = Auth::user();

        $mentions = ChatMessageMention::where('user_id', $user->id)
            // Exclude mentions on (soft-)deleted messages in SQL, so the 100-row
            // cap counts only actionable rows.
            ->whereHas('message')
            ->with(['message.author:id,name', 'message.channel.members:id,name'])
            ->orderByRaw('read_at IS NULL DESC')
            ->latest('id')
            ->limit(100)
            ->get();

        return view('chat.mentions', compact('mentions'));
    }

    // ===================================================================
    // Conversations
    // ===================================================================

    public function storeChannel(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'is_private' => 'sometimes|boolean',
            'members' => 'array',
            'members.*' => ['integer', Rule::exists('users', 'id')->where('type', 'internal')],
        ]);

        $channel = $this->chat->createChannel(
            $user,
            $data['name'],
            $data['description'] ?? null,
            $data['members'] ?? [],
            $request->boolean('is_private'),
        );

        return redirect()->route('chat.show', $channel)->with('success', __('portal.chat.channel_created'));
    }

    public function startDm(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'members' => 'required|array|min:1',
            'members.*' => ['integer', Rule::exists('users', 'id')->where('type', 'internal')],
        ]);

        $others = collect($data['members'])->map(fn ($i) => (int) $i)->reject(fn ($i) => $i === $user->id)->values();
        if ($others->isEmpty()) {
            return back()->withErrors(['members' => __('portal.chat.dm_pick_someone')]);
        }

        $channel = $this->chat->resolveDirectChannel($user, $others->all());

        return redirect()->route('chat.show', $channel);
    }

    /** Add members to a team channel (creator / admin / manager). */
    public function addMembers(Request $request, ChatChannel $channel)
    {
        $this->authorize('manageMembers', $channel);

        $data = $request->validate([
            'members' => 'required|array|min:1',
            'members.*' => ['integer', Rule::exists('users', 'id')->where('type', 'internal')],
        ]);

        $added = $this->chat->addMembers($channel, $data['members']);

        return redirect()->route('chat.show', $channel)
            ->with('success', __('portal.chat.members_added', ['count' => count($added)]));
    }

    /** Remove a member from a team channel; a member may also remove themselves (leave). */
    public function removeMember(ChatChannel $channel, User $member)
    {
        $isSelf = $member->id === Auth::id();

        if ($isSelf) {
            abort_unless(! $channel->isDm() && $channel->hasMember(Auth::user()), 403);
        } else {
            $this->authorize('manageMembers', $channel);
        }

        if (! $this->chat->removeMember($channel, $member->id)) {
            return back()->withErrors(['member' => __('portal.chat.cannot_remove')]);
        }

        if ($isSelf) {
            return redirect()->route('chat.index')->with('success', __('portal.chat.left_channel'));
        }

        return redirect()->route('chat.show', $channel)->with('success', __('portal.chat.member_removed'));
    }

    /** Directory of channels the user can discover but hasn't joined. */
    public function browse()
    {
        $user = Auth::user();

        $channels = ChatChannel::discoverableFor($user)->withCount('members')->get();

        $pending = ChatChannelJoinRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->pluck('chat_channel_id')
            ->all();

        return view('chat.browse', compact('channels', 'pending'));
    }

    /** Ask to join a public channel. */
    public function requestJoin(ChatChannel $channel)
    {
        $this->authorize('requestToJoin', $channel);

        $created = $this->chat->requestToJoin($channel, Auth::user());

        return redirect()->route('chat.browse')->with(
            'success',
            $created ? __('portal.chat.join_requested') : __('portal.chat.join_already_requested')
        );
    }

    /** Approve a pending join request (channel admin / creator / manager). */
    public function approveJoin(ChatChannel $channel, ChatChannelJoinRequest $joinRequest)
    {
        $this->authorize('manageMembers', $channel);
        abort_unless($joinRequest->chat_channel_id === $channel->id, 404);

        $this->chat->approveJoinRequest($joinRequest, Auth::user());

        return redirect()->route('chat.show', $channel)->with('success', __('portal.chat.join_approved'));
    }

    /** Decline a pending join request. */
    public function declineJoin(ChatChannel $channel, ChatChannelJoinRequest $joinRequest)
    {
        $this->authorize('manageMembers', $channel);
        abort_unless($joinRequest->chat_channel_id === $channel->id, 404);

        $this->chat->declineJoinRequest($joinRequest, Auth::user());

        return redirect()->route('chat.show', $channel)->with('success', __('portal.chat.join_declined'));
    }

    // ===================================================================
    // Messages
    // ===================================================================

    public function storeMessage(Request $request, ChatChannel $channel)
    {
        $this->authorize('post', $channel);

        $data = $request->validate([
            'body' => 'required_without:attachments|nullable|string|max:5000',
            'parent_id' => 'nullable|integer|exists:chat_messages,id',
            'attachments' => 'array|max:6',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip',
        ]);

        $message = $this->chat->send(
            $channel,
            Auth::user(),
            (string) ($data['body'] ?? ''),
            $data['parent_id'] ?? null,
            $request->file('attachments', []),
        );

        if ($request->ajax()) {
            $rs = $this->chat->readState($channel, Auth::id());
            return response()->json(['ok' => true, 'id' => $message->id, 'html' => $this->renderMessage($message, $rs)]);
        }

        return redirect()->route('chat.show', $channel);
    }

    public function updateMessage(Request $request, ChatMessage $message)
    {
        abort_unless($message->user_id === Auth::id(), 403);   // authors edit their own only

        $data = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $this->chat->edit($message, $data['body']);

        if ($request->ajax()) {
            $rs = $this->chat->readState($message->channel, Auth::id());
            return response()->json(['ok' => true, 'html' => $this->renderMessage($message->fresh($this->messageEagerLoads()), $rs)]);
        }

        return redirect()->route('chat.show', $message->chat_channel_id);
    }

    public function destroyMessage(Request $request, ChatMessage $message)
    {
        $user = Auth::user();
        $channel = $message->channel;

        // The author may always delete their own message. A manager may delete
        // others' messages ONLY in a channel they belong to — and never inside a
        // DM (private 1:1/group conversations are not manager-moderated).
        $isAuthor = $message->user_id === $user->id;
        $managerModerates = $user->isManager() && ! $channel->isDm() && $channel->hasMember($user);
        abort_unless($isAuthor || $managerModerates, 403);

        $this->chat->delete($message);

        if ($request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('chat.show', $message->chat_channel_id);
    }

    public function react(Request $request, ChatMessage $message)
    {
        $channel = $message->channel;
        $this->authorize('post', $channel);

        $data = $request->validate([
            'emoji' => ['required', 'string', Rule::in(self::EMOJIS)],
        ]);

        $this->chat->toggleReaction($message, Auth::user(), $data['emoji']);

        if ($request->ajax()) {
            $rs = $this->chat->readState($channel, Auth::id());
            return response()->json(['ok' => true, 'html' => $this->renderMessage($message->fresh($this->messageEagerLoads()), $rs)]);
        }

        return back();
    }

    // ===================================================================
    // JSON helpers (polling + pickers)
    // ===================================================================

    /** Forward poll: NEW root messages (id>after) + EXISTING roots changed since ?since (edits/reactions/reply-count). */
    public function messages(Request $request, ChatChannel $channel)
    {
        $this->authorize('view', $channel);

        $after = (int) $request->query('after', 0);
        $since = (int) $request->query('since', 0);

        $new = $channel->messages()->whereNull('parent_id')->where('id', '>', $after)
            ->with($this->messageEagerLoads())->withCount('replies')
            ->orderBy('id')->limit(100)->get();

        $updated = collect();
        if ($since > 0) {
            $updated = $channel->messages()->whereNull('parent_id')
                ->where('id', '<=', $after)
                ->where('updated_at', '>', \Illuminate\Support\Carbon::createFromTimestamp($since))
                ->with($this->messageEagerLoads())->withCount('replies')
                ->limit(100)->get();
        }

        // Only advance the read pointer when the tab is actually focused, so a
        // "Seen" receipt reflects genuine viewing — not just an open background tab.
        // (Mentions still only clear on an active open via show(), never here.)
        if ($request->boolean('focus')) {
            $this->chat->advanceRead($channel, Auth::user());
        }

        $rs = $this->chat->readState($channel, Auth::id());

        return response()->json([
            'messages' => $new->map(fn ($m) => ['id' => $m->id, 'html' => $this->renderMessage($m, $rs)])->values(),
            'updated' => $updated->map(fn ($m) => ['id' => $m->id, 'html' => $this->renderMessage($m, $rs)])->values(),
            'last_id' => (int) ($new->max('id') ?: $after),
            'since' => now()->timestamp,
            'reads' => $rs['reads'],
            'member_count' => $rs['memberCount'],
        ]);
    }

    /** Older root messages before ?before=id (the "load earlier" control). */
    public function older(Request $request, ChatChannel $channel)
    {
        $this->authorize('view', $channel);

        $before = (int) $request->query('before', 0);
        $query = $channel->messages()->whereNull('parent_id')
            ->with($this->messageEagerLoads())->withCount('replies')
            ->orderByDesc('id');
        if ($before > 0) {
            $query->where('id', '<', $before);
        }
        $older = $query->limit(self::PAGE_SIZE)->get()->reverse()->values();
        $rs = $this->chat->readState($channel, Auth::id());

        return response()->json([
            'messages' => $older->map(fn ($m) => ['id' => $m->id, 'html' => $this->renderMessage($m, $rs)])->values(),
            'first_id' => (int) ($older->min('id') ?: $before),
            'has_more' => $older->count() === self::PAGE_SIZE,
        ]);
    }

    /** Stream a chat attachment through the membership gate (files live on the private disk). */
    public function downloadAttachment(\App\Models\ChatAttachment $attachment)
    {
        $this->authorize('view', $attachment->message->channel);

        return \Illuminate\Support\Facades\Storage::disk($attachment->disk ?: 'private')
            ->download($attachment->path, $attachment->original_name);
    }

    /** Replies under one root message (thread pane). */
    public function thread(ChatChannel $channel, ChatMessage $message)
    {
        $this->authorize('view', $channel);
        abort_unless($message->chat_channel_id === $channel->id, 404);

        $replies = $message->replies()->with($this->messageEagerLoads())->get();

        return response()->json([
            'root' => $this->renderMessage($message->load($this->messageEagerLoads())),
            'replies' => $replies->map(fn ($m) => ['id' => $m->id, 'html' => $this->renderMessage($m)])->values(),
        ]);
    }

    /** Unread badges + mention count for the poller / bell. */
    public function poll()
    {
        return response()->json($this->chat->unreadCounts(Auth::user()));
    }

    /** Internal users for the @-mention autocomplete and DM/channel pickers. */
    public function members(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = User::where('type', 'internal');

        // Scope to a channel's members for @-mention (only members are mentionable).
        if ($request->filled('channel')) {
            $channel = ChatChannel::find($request->query('channel'));
            if ($channel && $channel->hasMember(Auth::user())) {
                $query->whereIn('id', $channel->members()->pluck('users.id'));
            }
        }

        if ($q !== '') {
            $query->where('name', 'like', '%'.$q.'%');
        }

        $users = $query->orderBy('name')->limit(20)->get(['id', 'name']);

        return response()->json($users->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values());
    }

    public function readAllMentions()
    {
        ChatMessageMention::where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', __('portal.chat.mentions_cleared'));
    }

    // ===================================================================
    // Internals
    // ===================================================================

    /** Internal users (excluding self) for the new-channel / new-DM pickers. */
    private function internalUsers()
    {
        return User::where('type', 'internal')
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function messageEagerLoads(): array
    {
        return [
            'author:id,name',
            'attachments',
            'reactions.user:id,name',
            'mentions.user:id,name',
        ];
    }

    private function renderMessage(ChatMessage $message, array $readState = []): string
    {
        if (! isset($message->replies_count)) {
            $message->loadCount('replies');
        }

        return view('chat._message', [
            'm' => $message,
            'emojis' => self::EMOJIS,
            'reads' => $readState['reads'] ?? [],
            'memberCount' => $readState['memberCount'] ?? 0,
        ])->render();
    }
}
