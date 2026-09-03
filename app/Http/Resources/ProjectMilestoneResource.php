<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectMilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'milestone_order' => (int) $this->milestone_order,
            'completion_percentage' => (int) $this->completion_percentage,
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toDateString(),
            'tasks' => ProjectTaskResource::collection($this->whenLoaded('tasks')),
            'tasks_count' => $this->whenCounted('tasks'),
        ];
    }
}
