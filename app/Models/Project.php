<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslations;

use App\Exceptions\BudgetLockedException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasAutoTranslations;

    protected array $translatable = ['title', 'description'];

    use HasFactory, HasUuidRouteKey, LogsActivity;

    /** Statuses at/after which the budget is locked (client approval onward). */
    public const LOCKED_BUDGET_STATUSES = ['awarded', 'in_progress', 'paused', 'completed', 'lost', 'cancelled'];

    /**
     * One-shot escape hatch allowing a budget change on a locked project. Set to
     * true ONLY by the approved change-request application path; never persisted.
     */
    public bool $allowBudgetOverride = false;

    protected static function booted(): void
    {
        // Model-level backstop for the budget lock: blocks any budget change once
        // the project is locked, unless an approved change request set the override.
        static::updating(function (self $project): void {
            if ($project->isDirty('budget') && ! $project->allowBudgetOverride) {
                // Use the status as it was BEFORE this save (status may change in the same update).
                $originalStatus = $project->getOriginal('status');
                if (in_array($originalStatus, self::LOCKED_BUDGET_STATUSES, true)) {
                    throw new BudgetLockedException(
                        'Project budget is locked after client approval. Submit an approved change request to adjust it.'
                    );
                }
            }
        });

        // Stamp the completion date on any path that marks a project completed
        // (the generic edit form, an import, etc.) — capacity revenue is recognized
        // by actual_end_date, so it must never be left empty when status=completed.
        static::saving(function (self $project): void {
            if ($project->status === 'completed' && empty($project->actual_end_date)) {
                $project->actual_end_date = now();
            }
        });
    }

    protected $fillable = [
        'client_id', 'title', 'description', 'scope', 'source_type', 'source_id',
        'status', 'budget', 'spent', 'completion_percentage',
        'start_date', 'end_date', 'actual_end_date',
        'project_manager_id', 'account_manager_id', 'team_members',
        'quoted_by', 'quote_file', 'quoted_at',
        'proposed_by', 'proposal_notes',
        'proposal_reviewed_by', 'proposal_reviewed_at', 'proposal_review_notes',
    ];

    protected $casts = [
        'team_members' => 'array',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_end_date' => 'date',
        'proposal_reviewed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'completion_percentage'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function quotedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quoted_by');
    }

    /** The internal staff member who proposed this project (proposal flow). */
    public function proposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    /** The manager / project manager who reviewed the proposal. */
    public function proposalReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposal_reviewed_by');
    }

    public function accountManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('milestone_order');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ProjectComment::class, 'commentable');
    }

    public function projectPeople(): HasMany
    {
        return $this->hasMany(ProjectPerson::class);
    }

    public function scopeChanges(): HasMany
    {
        return $this->hasMany(ProjectScopeChange::class);
    }

    /** Approved-but-not-yet-bought purchase requests = money committed against the budget. */
    public function committedSpend(): float
    {
        return (float) \App\Models\SpendRequest::where('project_id', $this->id)
            ->where('type', 'purchase')->where('status', 'approved')->sum('amount');
    }

    public function spendRequests(): HasMany
    {
        return $this->hasMany(SpendRequest::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(ProjectExpense::class);
    }

    public function feedback()
    {
        return $this->hasOne(ProjectFeedback::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(ProjectDeliverable::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(ProjectComplaint::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ProjectRequest::class);
    }

    public function getTeamMembers()
    {
        return $this->projectPeople()->with('user')->get()->pluck('user');
    }

    public function getProjectManager()
    {
        return $this->projectPeople()->where('role', 'project_manager')->first()?->user;
    }

    /**
     * Check if user can view the project
     */
    public function canUserView(User $user): bool
    {
        // Managers can view any project
        if ($user->isManager()) {
            return true;
        }

        // Client can view their own projects
        if ($this->client_id === $user->id) {
            return true;
        }

        // Proposal reviewers (global project managers; managers are already
        // covered above) can open a pending proposal to review it, and whoever
        // reviewed it keeps access to the outcome afterward.
        if ($this->isProposed() && $this->canUserReviewProposal($user)) {
            return true;
        }
        if ($this->proposal_reviewed_by === $user->id) {
            return true;
        }

        // The assigned project / account manager can always view their project,
        // even when only the column is set (e.g. a quote accepted -> order
        // conversion sets project_manager_id without a projectPeople pivot row).
        // hasAssignedManagementAccess() already trusts these columns for editing,
        // so viewing must too — otherwise the PM who accepts a quote is locked out
        // of (gets 403 on) the very project they just created.
        if ($this->project_manager_id === $user->id || $this->account_manager_id === $user->id) {
            return true;
        }

        // Check if user is part of project team
        return $this->projectPeople()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is the Project Manager (via pivot)
     */
    public function isUserProjectManager(User $user): bool
    {
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();

        return $projectPerson && $projectPerson->role === 'project_manager';
    }

    /**
     * Check if user can fully edit the project (PM or Manager or user with can_edit=true)
     */
    public function hasAssignedManagementAccess(User $user): bool
    {
        if (! $user->isInternal()) {
            return false;
        }

        // A top-level manager can manage any project (consistent with canUserView
        // / canUserAddComments, which already grant managers). Without this, a
        // manager who isn't the assigned PM saw projects but couldn't edit/manage
        // their tasks — the per-task edit modal rendered no manageable fields.
        if ($user->isManager()) {
            return true;
        }

        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();

        if ($projectPerson?->can_edit) {
            return true;
        }

        if ($projectPerson && in_array($projectPerson->role, ['project_manager', 'account_manager'], true)) {
            return true;
        }

        return $this->project_manager_id === $user->id || $this->account_manager_id === $user->id;
    }

    public function canUserEdit(User $user): bool
    {
        return $this->hasAssignedManagementAccess($user);
    }

    /**
     * Check if user is Account Manager
     */
    public function isUserAccountManager(User $user): bool
    {
        return $this->account_manager_id === $user->id;
    }

    /**
     * Check if user can manage team (add/remove members, edit roles)
     */
    public function canUserManageTeam(User $user): bool
    {
        return $this->hasAssignedManagementAccess($user);
    }

    /**
     * Check if user can create/edit/delete milestones
     */
    public function canUserManageMilestones(User $user): bool
    {
        return $this->hasAssignedManagementAccess($user);
    }

    /**
     * Check if user can create/delete tasks
     */
    public function canUserManageTasks(User $user): bool
    {
        return $this->hasAssignedManagementAccess($user);
    }

    /**
     * Check if user can add comments to the project
     */
    public function canUserAddComments(User $user): bool
    {
        // Super Admin / Manager can always add comments
        if ($user->isManager()) {
            return true;
        }

        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) {
            return true;
        }

        // Project Manager can add comments
        if ($this->isUserProjectManager($user)) {
            return true;
        }

        // Account Manager can add comments
        if ($this->isUserAccountManager($user)) {
            return true;
        }

        // Client can add comments to their own project
        if ($this->client_id === $user->id) {
            return true;
        }

        // Regular employees cannot add comments (only view)
        return false;
    }

    /**
     * Check if user can update a task (normal employees can only update status to completed)
     */
    public function canUserUpdateTask(User $user, ProjectTask $task): bool
    {
        if ($this->hasAssignedManagementAccess($user)) {
            return true;
        }

        // Regular employees cannot update tasks (only view)
        return false;
    }

    /**
     * Check if user can manage expenses
     */
    public function canUserManageExpenses(User $user): bool
    {
        return $this->hasAssignedManagementAccess($user);
    }

    public function isBudgetLocked(): bool
    {
        return in_array($this->status, self::LOCKED_BUDGET_STATUSES, true);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, ['completed', 'lost', 'cancelled'], true);
    }

    public function hasPendingScopeChanges(): bool
    {
        return $this->scopeChanges()->where('status', 'pending')->exists();
    }

    public function hasIncompleteMilestones(): bool
    {
        return $this->milestones()->where('status', '!=', 'completed')->exists();
    }

    public function hasOpenTasks(): bool
    {
        return $this->tasks()->where('status', '!=', 'completed')->exists();
    }

    /**
     * Check if user can manage scope changes
     */
    public function canUserManageScopeChanges(User $user): bool
    {
        // Only Super Admin / Manager can approve/reject scope changes
        return $user->isManager();
    }

    // Status helpers
    public function isProposed(): bool
    {
        return $this->status === 'proposed';
    }

    /**
     * Whether the given user may review (approve/reject) project proposals.
     * Managers and global project managers can; regular employees cannot.
     */
    public function canUserReviewProposal(User $user): bool
    {
        return $user->isManager() || $user->isProjectManager();
    }

    public function isPlanning(): bool
    {
        return $this->status === 'planning';
    }

    public function isQuoted(): bool
    {
        return $this->status === 'quoted';
    }

    public function isAwarded(): bool
    {
        return $this->status === 'awarded';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isLost(): bool
    {
        return $this->status === 'lost';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // Legacy aliases
    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isOnHold(): bool
    {
        return $this->status === 'paused';
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'proposed' => 'warning',
            'planning' => 'info',
            'quoted' => 'primary',
            'awarded' => 'success',
            'in_progress' => 'success',
            'paused' => 'warning',
            'completed' => 'success',
            'lost' => 'secondary',
            'cancelled' => 'danger',
            // Legacy
            'active' => 'success',
            'on_hold' => 'warning',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'proposed' => __('portal.projects.status.proposed'),
            'planning' => __('portal.projects.status.planning'),
            'quoted' => __('portal.projects.status.quoted'),
            'awarded' => __('portal.projects.status.awarded'),
            'in_progress' => __('portal.projects.status.in_progress'),
            'paused' => __('portal.projects.status.paused'),
            'completed' => __('portal.projects.status.completed'),
            'lost' => __('portal.projects.status.lost'),
            'cancelled' => __('portal.projects.status.cancelled'),
            default => str_replace('_', ' ', $this->status)
        };
    }

    public function getBudgetRemaining()
    {
        return $this->budget ? ($this->budget - $this->spent) : 0;
    }

    public function isOverBudget(): bool
    {
        return $this->budget && $this->spent > $this->budget;
    }

    public function getDaysRemaining()
    {
        if (! $this->end_date) {
            return null;
        }

        return now()->diffInDays($this->end_date, false);
    }

    public function isOverdue(): bool
    {
        return $this->end_date && now()->greaterThan($this->end_date) && ! $this->isCompleted();
    }
}
