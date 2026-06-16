<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\User;
use App\Services\Projects\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    // ============================================
    // CLIENT SIDE
    // ============================================

    public function clientIndex()
    {
        $user = Auth::user();

        if (! $user->canUseClientProjectPortal()) {
            abort(403);
        }

        $projects = Project::where('client_id', $user->id)
            ->with(['projectPeople.user', 'milestones', 'tasks'])
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => Project::where('client_id', $user->id)->count(),
            'active' => Project::where('client_id', $user->id)->where('status', 'active')->count(),
            'completed' => Project::where('client_id', $user->id)->where('status', 'completed')->count(),
        ];

        return view('projects.client.index', compact('projects', 'stats'));
    }

    public function clientShow(Project $project)
    {
        $user = Auth::user();

        if (! $user->canUseClientProjectPortal()) {
            abort(403);
        }
        $this->authorize('view', $project);

        $project->load([
            'client', 'projectPeople.user',
            'milestones.tasks', 'milestones.comments.user',
            'tasks.assignedTo', 'tasks.comments.user',
            'comments.user', 'scopeChanges.reviewedBy', 'feedback',
            'deliverables.uploadedBy', 'requests.handledBy', 'complaints.resolvedBy',
            'documents.uploadedBy',
        ]);

        return view('projects.client.show', compact('project'));
    }

    public function addComment(Request $request, Project $project)
    {
        $user = Auth::user();

        // Check if user can add comments
        if (! $project->canUserAddComments($user)) {
            abort(403, 'You do not have permission to add comments to this project.');
        }

        $validated = $request->validate([
            'comment' => 'required|string',
            'commentable_type' => 'required|in:App\Models\Project,App\Models\ProjectMilestone,App\Models\ProjectTask',
            'commentable_id' => 'required|integer',
        ]);

        // Prevent IDOR: the comment target must belong to the authorized project,
        // otherwise a user could attach comments to projects/milestones/tasks they
        // don't own by supplying a foreign commentable_id.
        $belongsToProject = match ($validated['commentable_type']) {
            \App\Models\Project::class => (int) $validated['commentable_id'] === (int) $project->id,
            \App\Models\ProjectMilestone::class => $project->milestones()->whereKey($validated['commentable_id'])->exists(),
            \App\Models\ProjectTask::class => $project->tasks()->whereKey($validated['commentable_id'])->exists(),
            default => false,
        };

        if (! $belongsToProject) {
            abort(403, 'The comment target does not belong to this project.');
        }

        ProjectComment::create([
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
            'user_id' => $user->id,
            'comment' => $validated['comment'],
            'is_internal' => $user->isInternal(),
        ]);

        // Engagement: an internal team member leaving a comment is a "solution comment".
        if ($user->isInternal()) {
            app(\App\Services\EngagementService::class)->award($user, 'solution_comment', $project, null, 'Comment on '.$project->title);
        }

        return back()->with('success', 'Comment added successfully!');
    }

    // ============================================
    // MANAGER/INTERNAL SIDE
    // ============================================

    public function managerIndex(Request $request)
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        $projects = $this->projectService->getProjectsForUser($user, $request->all());
        $stats = $this->projectService->getProjectStats($user);

        return view('projects.manager.index', compact('projects', 'stats'));
    }

    public function kanban(Request $request)
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        $query = Project::with(['client', 'projectPeople.user']);

        if ($user->isEmployee()) {
            $query->whereHas('projectPeople', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $projects = $query->get();

        return view('projects.manager.kanban', compact('projects'));
    }

    public function updateStatus(Request $request, Project $project)
    {
        $user = Auth::user();

        // Check permissions
        if (! $project->canUserEdit($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:planning,quoted,awarded,in_progress,paused,completed,lost,cancelled',
        ]);

        $project->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Project status updated successfully',
            'project' => $project,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        if (! $user->isManager()) {
            abort(403, 'Only managers can create projects.');
        }

        $clients = User::where('type', 'client')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get all internal users (employees, managers, etc.)
        $employees = User::where('type', 'internal')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $managers = User::where('type', 'internal')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('projects.manager.create', compact('clients', 'employees', 'managers'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'scope' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'project_manager_id' => 'nullable|exists:users,id',
            'team_members' => 'nullable|array',
            'team_members.*' => 'exists:users,id',
        ]);

        $project = $this->projectService->createProject($validated);

        return redirect()->route('projects.manager.show', $project)
            ->with('success', 'Project created successfully!');
    }

    public function managerShow(Project $project)
    {
        $user = Auth::user();

        $this->authorize('view', $project);

        $project->load([
            'client', 'projectPeople.user',
            'milestones.tasks.assignedTo', 'milestones.comments.user',
            'tasks.assignedTo', 'tasks.comments.user',
            'comments.user', 'scopeChanges', 'expenses.loggedBy',
            'deliverables.uploadedBy', 'requests.handledBy', 'complaints.resolvedBy',
            'feedback.client',
        ]);

        // Get all internal users for team management (not just employees)
        $employees = User::where('type', 'internal')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Granular permissions
        $canEdit = $project->canUserEdit($user);
        $canManageTeam = $project->canUserManageTeam($user);
        $canManageMilestones = $project->canUserManageMilestones($user);
        $canManageTasks = $project->canUserManageTasks($user);
        $canManageExpenses = $project->canUserManageExpenses($user);
        $canAddComments = $project->canUserAddComments($user);
        $isProjectManager = $project->isUserProjectManager($user);

        // Get project activities with pagination
        $activities = \Spatie\Activitylog\Models\Activity::where('subject_id', $project->id)
            ->where('subject_type', Project::class)
            ->with('causer')
            ->latest()
            ->paginate(10);

        return view('projects.manager.show', compact(
            'project', 'user', 'employees', 'canEdit', 'canManageTeam', 'canManageMilestones',
            'canManageTasks', 'canManageExpenses', 'canAddComments', 'isProjectManager', 'activities'
        ));
    }

    public function update(Request $request, Project $project)
    {
        $user = Auth::user();

        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'client_id' => 'nullable|exists:users,id',
            'scope' => 'nullable|string',
            'status' => 'required|in:planning,quoted,awarded,in_progress,paused,completed,lost,cancelled',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($project->isBudgetLocked() && array_key_exists('budget', $validated) && (float) ($validated['budget'] ?? 0) !== (float) ($project->budget ?? 0)) {
            return back()->withErrors([
                'error' => 'Budget is locked after approval. Use a scope change request to adjust the commercial terms.',
            ]);
        }

        // Handle client change in project_people table
        $oldClientId = $project->client_id;
        $newClientId = $validated['client_id'] ?? null;

        if ($oldClientId != $newClientId) {
            // Remove old client from project_people if exists
            if ($oldClientId) {
                $project->projectPeople()->where('user_id', $oldClientId)->where('role', 'client')->delete();
            }

            // Add new client to project_people if provided
            if ($newClientId) {
                \App\Models\ProjectPerson::firstOrCreate(
                    [
                        'project_id' => $project->id,
                        'user_id' => $newClientId,
                    ],
                    [
                        'role' => 'client',
                        'can_edit' => false,
                    ]
                );
            }
        }

        $project->update($validated);

        return back()->with('success', 'Project updated successfully!');
    }

    public function close(Request $request, Project $project)
    {
        $user = Auth::user();

        $this->authorize('update', $project);

        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled,lost',
        ]);

        if ($project->hasPendingScopeChanges()) {
            return back()->withErrors([
                'error' => 'Resolve pending scope change requests before closing this project.',
            ]);
        }

        if ($validated['status'] === 'completed' && ($project->hasIncompleteMilestones() || $project->hasOpenTasks())) {
            return back()->withErrors([
                'error' => 'Complete all milestones and tasks before marking this project as completed.',
            ]);
        }

        $wasCompleted = $project->status === 'completed';

        $project->update([
            'status' => $validated['status'],
            'actual_end_date' => now(),
            'completion_percentage' => $validated['status'] === 'completed' ? 100 : $project->completion_percentage,
        ]);

        // "Project closed" — credit the PM / account manager, gated behind their
        // monthly closed target. Only on the transition INTO completed, and once
        // per project per user (so reopen→reclose can never re-award).
        if ($validated['status'] === 'completed' && ! $wasCompleted) {
            $gate = app(\App\Services\Targets\TargetPointsGate::class);
            foreach (collect([$project->project_manager_id, $project->account_manager_id])->filter()->unique() as $uid) {
                $alreadyAwarded = \App\Models\EngagementLog::where('action', 'project_closed')
                    ->where('subject_type', \App\Models\Project::class)
                    ->where('subject_id', $project->id)
                    ->where('user_id', $uid)->exists();
                if (! $alreadyAwarded && ($u = \App\Models\User::find($uid))) {
                    $gate->awardIfEarned($u, 'project_closed', $project, 'Project completed: '.$project->title);
                }
            }
        }

        return back()->with('success', 'Project closed successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        try {
            $project->delete();
        } catch (\Throwable $e) {
            // e.g. a foreign-key restriction from a linked record.
            return back()->with('error', __('portal.projects_manager.index.delete_failed'));
        }

        return redirect()->route('projects.manager.index')
            ->with('success', __('portal.projects_manager.index.deleted'));
    }

    public function addTeamMember(Request $request, Project $project)
    {
        $user = Auth::user();

        $this->authorize('manageTeam', $project);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:employee,project_manager,account_manager',
            'can_edit' => 'boolean',
        ]);

        // Check if user already in project
        if ($project->projectPeople()->where('user_id', $validated['user_id'])->exists()) {
            return back()->withErrors(['error' => 'User is already in this project.']);
        }

        // Check if trying to add project manager when one already exists
        if ($validated['role'] === 'project_manager' && $project->projectPeople()->where('role', 'project_manager')->exists()) {
            return back()->withErrors(['error' => 'Project already has a project manager. Only one project manager is allowed per project.']);
        }

        // Handle checkbox: if not present, set to false or default based on role
        $canEdit = $request->has('can_edit') ? (bool) $validated['can_edit'] : ($validated['role'] === 'project_manager' || $validated['role'] === 'account_manager');

        \App\Models\ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'can_edit' => $canEdit,
        ]);

        // Update project if adding as PM or Account Manager
        if ($validated['role'] === 'project_manager') {
            $project->update(['project_manager_id' => $validated['user_id']]);
        }
        if ($validated['role'] === 'account_manager') {
            $project->update(['account_manager_id' => $validated['user_id']]);
        }

        return back()->with('success', 'Team member added successfully!');
    }

    public function updateTeamMember(Request $request, \App\Models\ProjectPerson $projectPerson)
    {
        $user = Auth::user();

        $this->authorize('manageTeam', $projectPerson->project);

        $validated = $request->validate([
            'role' => 'required|in:employee,project_manager,account_manager,client',
            'can_edit' => 'boolean',
        ]);

        // Check if trying to change to project manager when one already exists (and it's not the same person)
        if ($validated['role'] === 'project_manager' &&
            $projectPerson->role !== 'project_manager' &&
            $projectPerson->project->projectPeople()->where('role', 'project_manager')->exists()) {
            return back()->withErrors(['error' => 'Project already has a project manager. Only one project manager is allowed per project.']);
        }

        // Handle checkbox: if not present, set to false
        $validated['can_edit'] = $request->has('can_edit') ? (bool) $validated['can_edit'] : false;

        $oldRole = $projectPerson->role;
        $projectPerson->update($validated);

        // Update project if changing to PM or Account Manager
        if ($validated['role'] === 'project_manager') {
            // Clear any existing project manager first
            $projectPerson->project->projectPeople()
                ->where('role', 'project_manager')
                ->where('id', '!=', $projectPerson->id)
                ->update(['role' => 'employee']);

            $projectPerson->project->update(['project_manager_id' => $projectPerson->user_id]);
        }
        if ($validated['role'] === 'account_manager') {
            $projectPerson->project->update(['account_manager_id' => $projectPerson->user_id]);
        }

        // Clear project references if changing away from PM or Account Manager
        if ($oldRole === 'project_manager' && $validated['role'] !== 'project_manager') {
            $projectPerson->project->update(['project_manager_id' => null]);
        }
        if ($oldRole === 'account_manager' && $validated['role'] !== 'account_manager') {
            $projectPerson->project->update(['account_manager_id' => null]);
        }

        return back()->with('success', 'Team member updated successfully!');
    }

    public function removeTeamMember(\App\Models\ProjectPerson $projectPerson)
    {
        $user = Auth::user();

        $this->authorize('manageTeam', $projectPerson->project);

        $project = $projectPerson->project;
        $removedRole = $projectPerson->role;

        $projectPerson->delete();

        // If removed user was project manager, clear the project_manager_id
        if ($removedRole === 'project_manager') {
            $project->update(['project_manager_id' => null]);
        }

        // If removed user was account manager, clear the account_manager_id
        if ($removedRole === 'account_manager') {
            $project->update(['account_manager_id' => null]);
        }

        return back()->with('success', 'Team member removed successfully!');
    }
}
