<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatChannelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $viewer = $request->user();
        $unread = $viewer ? app(\App\Services\ChatService::class)->unreadCounts($viewer) : ['channels' => []];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->displayName($viewer),
            'description' => $this->description,
            'is_private' => (bool) $this->is_private,
            'is_dm' => $this->isDm(),
            'last_message_at' => $this->last_message_at?->toIso8601String(),
            'unread_count' => (int) ($unread['channels'][$this->id] ?? 0),
            'members_count' => $this->whenCounted('members'),
            'members' => $this->whenLoaded('members', fn () => $this->members->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name,
            ])->values()),
        ];
    }
}
