<?php

namespace App\Http\Resources;

use App\Support\MobileDeepLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'channel_id' => $this->chat_channel_id,
            'parent_id' => $this->parent_id,
            'body' => $this->body,
            'edited' => $this->wasEdited(),
            'author' => $this->whenLoaded('author', fn () => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ]),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->original_name,
                'mime' => $a->mime,
                'size' => (int) $a->size,
                'is_image' => $a->isImage(),
                'url' => url('/api/v1/chat/attachments/'.$a->id),
            ])->values()),
            'reactions' => $this->whenLoaded('reactions', fn () => $this->reactions->groupBy('emoji')->map(fn ($group, $emoji) => [
                'emoji' => $emoji,
                'count' => $group->count(),
                'user_ids' => $group->pluck('user_id')->values(),
            ])->values()),
            'mentions' => $this->whenLoaded('mentions', fn () => $this->mentions->pluck('user_id')->values()),
            'replies_count' => $this->whenCounted('replies'),
            'created_at' => $this->created_at?->toIso8601String(),
            'deep_link' => MobileDeepLink::absolute('chat/'.$this->chat_channel_id),
        ];
    }
}
