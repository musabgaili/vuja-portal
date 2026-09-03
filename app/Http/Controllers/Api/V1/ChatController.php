<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatChannelResource;
use App\Http\Resources\ChatMessageResource;
use App\Models\ChatAttachment;
use App\Models\ChatChannel;
use App\Models\ChatChannelJoinRequest;
use App\Models\ChatMessage;
use App\Models\ChatMessageMention;
use App\Models\User;
use App\Services\ChatService;
use App\Services\Fcm\FcmPushService;
use App\Support\MobileDeepLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public const EMOJIS = ['👍', '✅', '🎉', '❤️', '👀', '🙏', '🔥', '😄'];

    public const PAGE_SIZE = 50;

    public function __construct(
        private ChatService $chat,
        private FcmPushService $fcm,
    ) {}

    public function channels(Request $request)
    {
        $channels = ChatChannel::forUser($request->user())
            ->withCount('members')
            ->with('members:id,name')
            ->get();

        return ChatChannelResource::collection($channels);
    }

    public function storeChannel(Request $request): JsonResponse
    {
        $user = $request->user();

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

        return response()->json([
            'channel' => (new ChatChannelResource($channel->loadCount('members')))->resolve(),
        ], 201);
    }

    public function startDm(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'members' => 'required|array|min:1',
            'members.*' => ['integer', Rule::exists('users', 'id')->where('type', 'internal')],
        ]);

        $others = collect($data['members'])->map(fn ($i) => (int) $i)->reject(fn ($i) => $i === $user->id)->values();
        abort_if($others->isEmpty(), 422, 'Pick at least one other member.');

        $channel = $this->chat->resolveDirectChannel($user, $others->all());

        return response()->json([
            'channel' => (new ChatChannelResource($channel->load('members:id,name')))->resolve(),
        ], 201);
    }

    public function browse(Request $request): JsonResponse
    {
        $user = $request->user();
        $channels = ChatChannel::discoverableFor($user)->withCount('members')->get();
        $pending = ChatChannelJoinRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->pluck('chat_channel_id')
            ->all();

        return response()->json([
            'items' => ChatChannelResource::collection($channels)->resolve(),
            'pending_join_channel_ids' => $pending,
        ]);
    }

    public function requestJoin(Request $request, ChatChannel $channel): JsonResponse
    {
        $this->authorize('requestToJoin', $channel);
        $created = $this->chat->requestToJoin($channel, $request->user());

        return response()->json(['requested' => $created]);
    }

    public function messages(Request $request, ChatChannel $channel): JsonResponse
    {
        $this->authorize('view', $channel);

        $before = (int) $request->integer('before', 0);
        $query = $channel->messages()->whereNull('parent_id')
            ->with($this->eagerLoads())
            ->withCount('replies')
            ->orderByDesc('id');

        if ($before > 0) {
            $query->where('id', '<', $before);
        }

        $messages = $query->limit(self::PAGE_SIZE)->get()->reverse()->values();
        $this->chat->markRead($channel, $request->user());

        return response()->json([
            'items' => ChatMessageResource::collection($messages)->resolve(),
            'has_more' => $messages->count() === self::PAGE_SIZE,
            'first_id' => $messages->min('id'),
        ]);
    }

    public function storeMessage(Request $request, ChatChannel $channel): JsonResponse
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
            $request->user(),
            (string) ($data['body'] ?? ''),
            $data['parent_id'] ?? null,
            $request->file('attachments', []),
        );

        $message->load($this->eagerLoads())->loadCount('replies');
        $this->notifyChatRecipients($channel, $message, $request->user());

        return response()->json([
            'message' => (new ChatMessageResource($message))->resolve(),
        ], 201);
    }

    public function updateMessage(Request $request, ChatMessage $message): JsonResponse
    {
        abort_unless($message->user_id === $request->user()->id, 403);

        $data = $request->validate(['body' => 'required|string|max:5000']);
        $this->chat->edit($message, $data['body']);
        $message->load($this->eagerLoads())->loadCount('replies');

        return response()->json(['message' => (new ChatMessageResource($message))->resolve()]);
    }

    public function destroyMessage(Request $request, ChatMessage $message): JsonResponse
    {
        $user = $request->user();
        $channel = $message->channel;
        $isAuthor = $message->user_id === $user->id;
        $managerModerates = $user->isManager() && ! $channel->isDm() && $channel->hasMember($user);
        abort_unless($isAuthor || $managerModerates, 403);

        $this->chat->delete($message);

        return response()->json(['message' => 'Deleted.']);
    }

    public function react(Request $request, ChatMessage $message): JsonResponse
    {
        $this->authorize('post', $message->channel);

        $data = $request->validate([
            'emoji' => ['required', 'string', Rule::in(self::EMOJIS)],
        ]);

        $this->chat->toggleReaction($message, $request->user(), $data['emoji']);
        $message->load($this->eagerLoads())->loadCount('replies');

        return response()->json(['message' => (new ChatMessageResource($message))->resolve()]);
    }

    public function thread(Request $request, ChatChannel $channel, ChatMessage $message): JsonResponse
    {
        $this->authorize('view', $channel);
        abort_unless($message->chat_channel_id === $channel->id, 404);

        $replies = $message->replies()->with($this->eagerLoads())->get();

        return response()->json([
            'root' => (new ChatMessageResource($message->load($this->eagerLoads())))->resolve(),
            'replies' => ChatMessageResource::collection($replies)->resolve(),
        ]);
    }

    public function mentions(Request $request): JsonResponse
    {
        $user = $request->user();

        $mentions = ChatMessageMention::where('user_id', $user->id)
            ->whereHas('message')
            ->with(['message.author:id,name', 'message.channel'])
            ->orderByRaw('read_at IS NULL DESC')
            ->latest('id')
            ->limit(100)
            ->get()
            ->map(fn ($mn) => [
                'id' => $mn->id,
                'read_at' => $mn->read_at?->toIso8601String(),
                'message' => $mn->message ? (new ChatMessageResource(
                    $mn->message->load(['author:id,name', 'attachments', 'reactions'])
                ))->resolve() : null,
                'deep_link' => $mn->message
                    ? MobileDeepLink::absolute('chat/'.$mn->message->chat_channel_id)
                    : null,
            ]);

        return response()->json(['items' => $mentions]);
    }

    public function readAllMentions(Request $request): JsonResponse
    {
        ChatMessageMention::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function members(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $query = User::where('type', 'internal');

        if ($request->filled('channel')) {
            $channel = ChatChannel::find($request->query('channel'));
            if ($channel && $channel->hasMember($request->user())) {
                $query->whereIn('id', $channel->members()->pluck('users.id'));
            }
        }

        if ($q !== '') {
            $query->where('name', 'like', '%'.$q.'%');
        }

        $users = $query->orderBy('name')->limit(20)->get(['id', 'name']);

        return response()->json(['items' => $users]);
    }

    public function downloadAttachment(Request $request, ChatAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment->message->channel);

        return Storage::disk($attachment->disk ?: 'private')
            ->download($attachment->path, $attachment->original_name);
    }

    /** @return list<string> */
    private function eagerLoads(): array
    {
        return ['author:id,name', 'attachments', 'reactions', 'mentions'];
    }

    private function notifyChatRecipients(ChatChannel $channel, ChatMessage $message, User $author): void
    {
        $deepLink = MobileDeepLink::absolute('chat/'.$channel->id);
        $body = \Illuminate\Support\Str::limit($message->body, 120);

        foreach ($message->mentions as $mention) {
            if ($mention->user_id === $author->id) {
                continue;
            }
            $target = User::find($mention->user_id);
            if ($target) {
                $this->fcm->pushToUser($target, $author->name.' mentioned you', $body, $deepLink);
            }
        }

        if ($channel->isDm()) {
            foreach ($channel->members()->where('users.id', '!=', $author->id)->get() as $member) {
                $mentioned = $message->mentions->contains('user_id', $member->id);
                if (! $mentioned) {
                    $this->fcm->pushToUser($member, $author->name, $body, $deepLink);
                }
            }
        }
    }
}
