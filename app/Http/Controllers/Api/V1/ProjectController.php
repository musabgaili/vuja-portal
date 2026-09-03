<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Services\Projects\ProjectService;
use App\Support\MobileDeepLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(private ProjectService $projects) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = min(50, max(1, (int) $request->integer('per_page', 15)));

        $paginator = $this->projects->getProjectsForUser($user, $request->only(['search', 'status', 'pm', 'quoter']));
        $paginator->appends($request->query());

        return ProjectResource::collection($paginator)->additional([
            'meta' => [
                'stats' => $this->projects->getProjectStats($user),
            ],
        ]);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project->load([
            'client:id,name,email',
            'projectManager:id,name',
            'projectPeople.user:id,name',
            'milestones' => fn ($q) => $q->orderBy('milestone_order'),
            'milestones.tasks.assignedTo:id,name',
        ])->loadCount(['milestones', 'tasks']);

        $user = $request->user();

        return response()->json([
            'project' => (new ProjectResource($project))->resolve(),
            'permissions' => [
                'can_edit' => $project->canUserEdit($user),
                'can_manage_team' => $project->canUserManageTeam($user),
                'can_manage_milestones' => $project->canUserManageMilestones($user),
                'can_manage_tasks' => $project->canUserManageTasks($user),
            ],
            'deep_link' => MobileDeepLink::absolute('projects/'.$project->uuid),
        ]);
    }

    /** Propose a new project (any internal) or create outright (manager/PM). */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'scope' => 'nullable|string',
            'client_id' => 'nullable|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_manager_id' => 'nullable|exists:users,id',
            'team_members' => 'nullable|array',
            'team_members.*' => 'exists:users,id',
            'proposal_notes' => 'nullable|string',
        ]);

        if ($user->canManageProjects() && $request->boolean('create')) {
            abort_unless(! empty($validated['client_id']), 422, 'client_id is required to create a project.');
            $project = $this->projects->createProject($validated);
        } else {
            abort_unless($user->isInternal(), 403);
            $project = Project::create([
                'client_id' => $validated['client_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'scope' => $validated['scope'] ?? null,
                'budget' => $validated['budget'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => 'proposed',
                'proposal_notes' => $validated['proposal_notes'] ?? null,
                'proposed_by' => $user->id,
            ]);
            $this->projects->addProjectPerson($project, $user->id, 'employee', false);
        }

        return response()->json([
            'project' => (new ProjectResource($project->load('client:id,name')))->resolve(),
        ], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'scope' => 'nullable|string',
            'status' => 'sometimes|in:proposed,planning,quoted,awarded,in_progress,paused,completed,lost,cancelled',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'completion_percentage' => 'sometimes|integer|min:0|max:100',
        ]);

        $project->update($validated);

        return response()->json(['project' => (new ProjectResource($project->fresh()))->resolve()]);
    }

    public function close(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled,lost',
        ]);

        if ($project->hasPendingScopeChanges()) {
            return response()->json(['message' => 'Resolve pending scope changes first.'], 422);
        }

        if ($validated['status'] === 'completed' && ($project->hasIncompleteMilestones() || $project->hasOpenTasks())) {
            return response()->json(['message' => 'Complete all milestones and tasks first.'], 422);
        }

        $project->update([
            'status' => $validated['status'],
            'actual_end_date' => now(),
            'completion_percentage' => $validated['status'] === 'completed' ? 100 : $project->completion_percentage,
        ]);

        return response()->json(['project' => (new ProjectResource($project->fresh()))->resolve()]);
    }

    public function comments(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $comments = $project->comments()
            ->with('user:id,name')
            ->latest()
            ->limit(min(50, (int) $request->integer('limit', 30)))
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'comment' => $c->comment,
                'is_internal' => (bool) $c->is_internal,
                'internal_note' => (bool) $c->internal_note,
                'user' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name] : null,
                'created_at' => $c->created_at?->toIso8601String(),
            ]);

        return response()->json(['items' => $comments]);
    }

    public function addComment(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();
        abort_unless($project->canUserAddComments($user), 403);

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
            'internal_note' => 'sometimes|boolean',
        ]);

        $comment = ProjectComment::create([
            'commentable_type' => Project::class,
            'commentable_id' => $project->id,
            'user_id' => $user->id,
            'comment' => $validated['comment'],
            'is_internal' => $user->isInternal(),
            'internal_note' => $user->isInternal() && $request->boolean('internal_note'),
        ]);

        if ($user->isInternal()) {
            app(\App\Services\EngagementService::class)->award($user, 'solution_comment', $project, null, 'Comment on '.$project->title);
        }

        return response()->json(['comment' => [
            'id' => $comment->id,
            'comment' => $comment->comment,
            'created_at' => $comment->created_at?->toIso8601String(),
        ]], 201);
    }
}
