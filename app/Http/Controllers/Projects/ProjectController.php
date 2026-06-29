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
            'internal_note' => 'sometimes|boolean',
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
            // Internal note (hidden from client) — only staff may flag one.
            'internal_note' => $user->isInternal() && $request->boolean('internal_note'),
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
            'status' => 'required|in:proposed,planning,quoted,awarded,in_progress,paused,completed,lost,cancelled',
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

        if (! $user->canManageProjects()) {
            abort(403, 'Only managers and project managers can create projects.');
        }

        $clients = User::where('type', 'client')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get all internal users (employees, managers, etc.)
        $employees = User::where('type', 'internal')
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('name')
            ->get();

        $managers = User::where('type', 'internal')
            ->whereIn('status', ['active', 'pending'])
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

        $this->notifyProjectCreated($project, $user);

        return redirect()->route('projects.manager.show', $project)
            ->with('success', 'Project created successfully!');
    }

    /** Tell the assigned team (PM, account manager, assigned staff) a project was created. */
    private function notifyProjectCreated(Project $project, User $actor): void
    {
        $notifier = app(\App\Services\Notifier::class);
        foreach ($project->teamUserIds() as $uid) {
            if ((int) $uid === (int) $actor->id) {
                continue;
            }
            $target = User::find($uid);
            if (! $target || ! $target->isInternal()) {
                continue;
            }
            $notifier->email($target, 'project_created',
                __('portal.notif_prefs.mail.project_created_subject'),
                __('portal.notif_prefs.mail.project_created_heading'),
                __('portal.notif_prefs.mail.project_created_body', ['title' => $project->title, 'by' => $actor->name]),
                route('projects.manager.show', $project));
        }
    }

    // ============================================
    // PROJECT PROPOSALS (employee proposes → manager/PM approves to start)
    // ============================================

    /**
     * Show the "propose a project" form. Any internal staff member may propose.
     */
    public function proposeCreate()
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        // Client is optional on a proposal (the idea may pre-date a signed client).
        $clients = User::where('type', 'client')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('projects.manager.propose', compact('clients'));
    }

    /**
     * Persist a proposed project. It starts in the "proposed" status, owned by
     * the proposer (added to the team so they can track it), and waits for a
     * manager / project manager to approve it.
     */
    public function proposeStore(Request $request)
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'scope' => 'nullable|string',
            'proposal_notes' => 'nullable|string',
            'client_id' => 'nullable|exists:users,id',
            'budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            // Unregistered client captured inline (recorded as prospect, or invited).
            'new_client_name' => 'nullable|string|max:160',
            'new_client_email' => 'nullable|email|max:255',
            'new_client_phone' => 'nullable|string|max:40',
            'new_client_company' => 'nullable|string|max:160',
        ]);

        $action = $request->input('action') === 'invite' ? 'invite' : 'record';
        $hasNewClient = empty($validated['client_id']) && ! empty($validated['new_client_name']);

        // Inviting needs an email to send to.
        if ($action === 'invite' && $hasNewClient && empty($validated['new_client_email'])) {
            return back()->withInput()->withErrors(['new_client_email' => __('portal.projects_propose.invite_needs_email')]);
        }

        $provisioning = app(\App\Services\Clients\ClientProvisioningService::class);
        $title = $validated['title'];

        // The whole create is serialized per (proposer, title) and collapses a
        // repeat of the same still-pending proposal (a double-submit) into the
        // original row. Returns the outcome used to pick the flash message.
        $create = function () use ($validated, $user, $title, $action, $hasNewClient, $provisioning): string {
            $recent = Project::where('proposed_by', $user->id)
                ->where('status', 'proposed')
                ->where('title', $title)
                ->where('created_at', '>=', now()->subMinutes(10))
                ->exists();

            if ($recent) {
                return 'duplicate'; // identical proposal just filed — no-op.
            }

            $clientId = $validated['client_id'] ?? null;
            $prospect = ['name' => null, 'email' => null, 'phone' => null, 'company' => null];
            $invited = false;

            if ($hasNewClient) {
                if ($action === 'invite') {
                    // Create (or reuse) the client account and email an activation
                    // invite. Throws ClientEmailTakenException for staff emails.
                    $result = $provisioning->findOrCreateClient([
                        'name' => $validated['new_client_name'],
                        'email' => $validated['new_client_email'],
                        'phone' => $validated['new_client_phone'] ?? null,
                        'company' => $validated['new_client_company'] ?? null,
                    ], $user->id);
                    $clientId = $result['user']->id;
                    $invited = $provisioning->sendInvitation($result['user'], $user);
                } else {
                    // Record only — keep the client's details as free text.
                    $prospect = [
                        'name' => $validated['new_client_name'],
                        'email' => $validated['new_client_email'] ?? null,
                        'phone' => $validated['new_client_phone'] ?? null,
                        'company' => $validated['new_client_company'] ?? null,
                    ];
                }
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $user, $clientId, $prospect) {
                $project = Project::create([
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'scope' => $validated['scope'] ?? null,
                    'proposal_notes' => $validated['proposal_notes'] ?? null,
                    'client_id' => $clientId,
                    'prospect_name' => $prospect['name'],
                    'prospect_email' => $prospect['email'],
                    'prospect_phone' => $prospect['phone'],
                    'prospect_company' => $prospect['company'],
                    'budget' => $validated['budget'] ?? null,
                    'start_date' => $validated['start_date'] ?? null,
                    'end_date' => $validated['end_date'] ?? null,
                    'status' => 'proposed',
                    'proposed_by' => $user->id,
                ]);

                // Track the proposer on the team so it surfaces in their project
                // list (employees only see projects they belong to).
                \App\Models\ProjectPerson::firstOrCreate(
                    ['project_id' => $project->id, 'user_id' => $user->id],
                    ['role' => 'employee', 'can_edit' => false],
                );

                if ($clientId) {
                    \App\Models\ProjectPerson::firstOrCreate(
                        ['project_id' => $project->id, 'user_id' => $clientId],
                        ['role' => 'client', 'can_edit' => false],
                    );
                }
            });

            return $invited ? 'invited' : 'recorded';
        };

        $lockKey = 'propose:'.$user->id.':'.md5(mb_strtolower(trim($title)));

        try {
            try {
                $outcome = \Illuminate\Support\Facades\Cache::lock($lockKey, 10)->block(5, $create);
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                // Couldn't grab the lock in time — proceed without it (the dedupe
                // check inside still guards against an obvious duplicate).
                $outcome = $create();
            }
        } catch (\App\Exceptions\ClientEmailTakenException $e) {
            return back()->withInput()->withErrors(['new_client_email' => __('portal.quick_client.email_taken')]);
        }

        $message = $outcome === 'invited'
            ? __('portal.projects_propose.submitted_invited')
            : __('portal.projects_propose.submitted');

        return redirect()->route('projects.proposals.index')->with('success', $message);
    }

    /**
     * The proposals review queue. Reviewers (manager / project manager) see all
     * pending proposals; an employee sees the proposals they submitted.
     */
    public function proposalsIndex()
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        $isReviewer = $user->isManager() || $user->isProjectManager();

        $query = Project::where('status', 'proposed')
            ->with(['proposedBy', 'client'])
            ->latest();

        if (! $isReviewer) {
            $query->where('proposed_by', $user->id);
        }

        $proposals = $query->get();

        // Recently reviewed proposals (approved/rejected) for context.
        $reviewedQuery = Project::whereNotNull('proposal_reviewed_at')
            ->with(['proposedBy', 'proposalReviewedBy', 'client'])
            ->latest('proposal_reviewed_at')
            ->limit(15);

        if (! $isReviewer) {
            $reviewedQuery->where('proposed_by', $user->id);
        }

        $reviewed = $reviewedQuery->get();

        return view('projects.manager.proposals', compact('proposals', 'reviewed', 'isReviewer'));
    }

    /**
     * Approve a proposal → the project starts (moves into the planning pipeline).
     */
    public function approveProposal(Request $request, Project $project)
    {
        $user = Auth::user();

        abort_unless($project->canUserReviewProposal($user), 403, __('portal.projects_propose.review_forbidden'));
        abort_unless($project->isProposed(), 422, __('portal.projects_propose.not_pending'));

        $project->update([
            'status' => 'planning',
            'proposal_reviewed_by' => $user->id,
            'proposal_reviewed_at' => now(),
            'proposal_review_notes' => $request->input('review_notes'),
        ]);

        app(\App\Services\Notifier::class)->email(
            $project->proposedBy, 'project_proposal_reviewed',
            __('portal.notif_prefs.mail.proposal_subject'),
            __('portal.notif_prefs.mail.proposal_approved_heading'),
            __('portal.notif_prefs.mail.proposal_approved_body', ['title' => $project->title]),
            route('projects.manager.show', $project),
        );

        return redirect()->route('projects.manager.show', $project)
            ->with('success', __('portal.projects_propose.approved'));
    }

    /**
     * Reject a proposal with a required comment the proposer will see.
     */
    public function rejectProposal(Request $request, Project $project)
    {
        $user = Auth::user();

        abort_unless($project->canUserReviewProposal($user), 403, __('portal.projects_propose.review_forbidden'));
        abort_unless($project->isProposed(), 422, __('portal.projects_propose.not_pending'));

        $validated = $request->validate([
            'review_notes' => 'required|string|max:2000',
        ]);

        $project->update([
            'status' => 'cancelled',
            'proposal_reviewed_by' => $user->id,
            'proposal_reviewed_at' => now(),
            'proposal_review_notes' => $validated['review_notes'],
        ]);

        app(\App\Services\Notifier::class)->email(
            $project->proposedBy, 'project_proposal_reviewed',
            __('portal.notif_prefs.mail.proposal_subject'),
            __('portal.notif_prefs.mail.proposal_sentback_heading'),
            __('portal.notif_prefs.mail.proposal_sentback_body', ['title' => $project->title])."\n\n".$validated['review_notes'],
            route('projects.manager.show', $project),
        );

        return redirect()->route('projects.proposals.index')
            ->with('success', __('portal.projects_propose.rejected'));
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
            ->whereIn('status', ['active', 'pending'])
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
            'status' => 'required|in:proposed,planning,quoted,awarded,in_progress,paused,completed,lost,cancelled',
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
