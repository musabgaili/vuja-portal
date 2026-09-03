<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Projects\UpdateProjectProgressAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectMilestoneResource;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectMilestoneController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $milestones = $project->milestones()
            ->withCount('tasks')
            ->orderBy('milestone_order')
            ->get();

        return ProjectMilestoneResource::collection($milestones);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('manageMilestones', $project);
        abort_if($project->isCompleted(), 422, 'Completed projects cannot receive new milestones.');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $validated['project_id'] = $project->id;
        $validated['milestone_order'] = (int) $project->milestones()->max('milestone_order') + 1;

        $milestone = ProjectMilestone::create($validated);
        app(UpdateProjectProgressAction::class)->execute($project);

        return response()->json([
            'milestone' => (new ProjectMilestoneResource($milestone))->resolve(),
        ], 201);
    }

    public function update(Request $request, ProjectMilestone $milestone): JsonResponse
    {
        $this->authorize('update', $milestone);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'due_date' => 'nullable|date',
        ]);

        $totalTasks = $milestone->tasks()->count();
        if ($totalTasks > 0) {
            $completedTasks = $milestone->tasks()->where('status', 'completed')->count();
            $validated['completion_percentage'] = round(($completedTasks / $totalTasks) * 100);
        }

        if (($validated['status'] ?? null) === 'completed') {
            $validated['completed_at'] = now();
            $validated['completion_percentage'] = 100;
        }

        $milestone->update($validated);
        app(UpdateProjectProgressAction::class)->execute($milestone->project);

        return response()->json([
            'milestone' => (new ProjectMilestoneResource($milestone->fresh()))->resolve(),
        ]);
    }
}
