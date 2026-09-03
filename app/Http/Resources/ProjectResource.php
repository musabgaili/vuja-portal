<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'budget' => $this->budget,
            'completion_percentage' => (int) $this->completion_percentage,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ]),
            'project_manager' => $this->whenLoaded('projectManager', fn () => $this->projectManager ? [
                'id' => $this->projectManager->id,
                'name' => $this->projectManager->name,
            ] : null),
            'team' => $this->whenLoaded('projectPeople', fn () => $this->projectPeople->map(fn ($p) => [
                'user_id' => $p->user_id,
                'name' => $p->user?->name,
                'role' => $p->role,
            ])->values()),
            'milestones_count' => $this->whenCounted('milestones'),
            'tasks_count' => $this->whenCounted('tasks'),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
