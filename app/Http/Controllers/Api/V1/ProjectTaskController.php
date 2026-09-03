<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Projects\UpdateProjectProgressAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectTaskResource;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function index(Request $request, Project $project)
    {
        $this->authorize('view', $project);

        $query = $project->tasks()->with(['assignedTo:id,name', 'milestone:id,title']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return ProjectTaskResource::collection(
            $query->orderBy('id')->paginate(min(100, (int) $request->integer('per_page', 50)))
        );
    }

    /** Kanban columns grouped by status. */
    public function kanban(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['assignedTo:id,name'])
            ->orderBy('id')
            ->get();

        $columns = [];
        foreach (['todo', 'in_progress', 'review', 'completed', 'blocked'] as $status) {
            $columns[$status] = ProjectTaskResource::collection(
                $tasks->where('status', $status)->values()
            )->resolve();
        }

        return response()->json(['columns' => $columns]);
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();
        $this->authorize('manageTasks', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
        ]);

        if (! empty($validated['milestone_id'])) {
            abort_unless(
                $project->milestones()->whereKey($validated['milestone_id'])->exists(),
                422,
                'Milestone does not belong to this project.'
            );
        }

        $validated['project_id'] = $project->id;
        $validated['created_by'] = $user->id;

        $task = ProjectTask::create($validated);

        if ($task->assigned_to && (int) $task->assigned_to !== (int) $user->id) {
            app(\App\Services\Fcm\FcmPushService::class)->pushToUser(
                \App\Models\User::find($task->assigned_to),
                'New task assigned',
                $task->title,
                'vujade://app/projects/'.$project->uuid.'/tasks/'.$task->id,
            );
        }

        app(UpdateProjectProgressAction::class)->execute($project);

        return response()->json([
            'task' => (new ProjectTaskResource($task->load('assignedTo:id,name')))->resolve(),
        ], 201);
    }

    public function update(Request $request, ProjectTask $projectTask): JsonResponse
    {
        $user = $request->user();
        abort_unless($projectTask->project->canUserUpdateTask($user, $projectTask), 403);

        $canManage = $projectTask->project->canUserManageTasks($user);
        $isAssignee = (int) $projectTask->assigned_to === (int) $user->id;

        if (! $canManage && $isAssignee) {
            $validated = $request->validate([
                'status' => 'required|in:todo,in_progress,review,completed,blocked',
            ]);
        } else {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:todo,in_progress,review,completed,blocked,cancelled',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'milestone_id' => 'nullable|exists:project_milestones,id',
                'assigned_to' => 'nullable|exists:users,id',
                'due_date' => 'nullable|date',
                'actual_hours' => 'nullable|integer|min:0',
            ]);
        }

        if (($validated['status'] ?? null) === 'completed') {
            $validated['completed_at'] = now();
        }

        $projectTask->update($validated);
        app(UpdateProjectProgressAction::class)->execute($projectTask->project);

        return response()->json([
            'task' => (new ProjectTaskResource($projectTask->fresh(['assignedTo:id,name'])))->resolve(),
        ]);
    }

    public function destroy(Request $request, ProjectTask $projectTask): JsonResponse
    {
        $this->authorize('manageTasks', $projectTask->project);
        $project = $projectTask->project;
        $projectTask->delete();
        app(UpdateProjectProgressAction::class)->execute($project);

        return response()->json(['message' => 'Task deleted.']);
    }
}
