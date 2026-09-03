<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'milestone_id' => $this->milestone_id,
            'assigned_to' => $this->assigned_to,
            'assignee' => $this->whenLoaded('assignedTo', fn () => $this->assignedTo ? [
                'id' => $this->assignedTo->id,
                'name' => $this->assignedTo->name,
            ] : null),
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toDateString(),
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
