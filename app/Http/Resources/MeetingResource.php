<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** A meeting for the mobile API (the "with" party is resolved per viewer). */
class MeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $me = $request->user();
        $other = $me && $me->isClient()
            ? $this->teamMember
            : ($this->team_member_id === $me?->id ? $this->client : $this->teamMember);

        return [
            'id' => $this->getRouteKey(),   // uuid route key
            'title' => $this->title,
            'description' => $this->description,
            'notes' => $this->meeting_notes,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'scheduled_at' => optional($this->scheduled_at)->toIso8601String(),
            'ends_at' => $this->scheduled_at ? $this->getEndTime()->toIso8601String() : null,
            'duration_minutes' => (int) $this->duration_minutes,
            'meeting_link' => $this->meeting_link,
            'with' => $other ? [
                'name' => $other->name,
                'email' => $other->email,
                'phone' => $other->phone,
            ] : null,
            'attendees' => $this->whenLoaded('attendees', fn () => $this->attendees->map(fn ($a) => [
                'name' => $a->user?->name,
                'status' => $a->status,
            ])->values()),
            'can_confirm' => (bool) ($me && (int) $this->team_member_id === (int) $me->id && $this->isScheduled()),
            'can_complete' => (bool) ($me && ! $me->isClient() && ! $this->isCompleted() && ! $this->isCancelled()),
        ];
    }
}
