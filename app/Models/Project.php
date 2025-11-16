<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'client_id', 'title', 'description', 'scope', 'source_type', 'source_id',
        'status', 'budget', 'spent', 'completion_percentage',
        'start_date', 'end_date', 'actual_end_date',
        'project_manager_id', 'account_manager_id', 'team_members',
        'quoted_by', 'quote_file', 'quoted_at',
    ];

    protected $casts = [
        'team_members' => 'array',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'actual_end_date' => 'date',
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
        if ($user->isManager()) return true;
        
        // Client can view their own projects
        if ($this->client_id === $user->id) return true;
        
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
    public function canUserEdit(User $user): bool
    {
        // Super Admin / Manager can edit any project
        if ($user->isManager()) return true;
        
        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) return true;
        
        // Project Manager can edit (legacy check)
        return $this->isUserProjectManager($user);
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
        // Super Admin / Manager can manage team
        if ($user->isManager()) return true;
        
        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) return true;
        
        // Project Manager can manage team
        if ($this->isUserProjectManager($user)) return true;
        
        // Account Manager can manage team
        return $this->isUserAccountManager($user);
    }

    /**
     * Check if user can create/edit/delete milestones
     */
    public function canUserManageMilestones(User $user): bool
    {
        // Super Admin / Manager can manage milestones
        if ($user->isManager()) return true;
        
        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) return true;
        
        // Project Manager can manage milestones
        return $this->isUserProjectManager($user);
    }

    /**
     * Check if user can create/delete tasks
     */
    public function canUserManageTasks(User $user): bool
    {
        // Super Admin / Manager can manage tasks
        if ($user->isManager()) return true;
        
        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) return true;
        
        // Project Manager can manage tasks
        return $this->isUserProjectManager($user);
    }

    /**
     * Check if user can add comments to the project
     */
    public function canUserAddComments(User $user): bool
    {
        // Super Admin / Manager can always add comments
        if ($user->isManager()) return true;
        
        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) return true;
        
        // Project Manager can add comments
        if ($this->isUserProjectManager($user)) return true;
        
        // Account Manager can add comments
        if ($this->isUserAccountManager($user)) return true;
        
        // Client can add comments to their own project
        if ($this->client_id === $user->id) return true;
        
        // Regular employees cannot add comments (only view)
        return false;
    }

    /**
     * Check if user can update a task (normal employees can only update status to completed)
     */
    public function canUserUpdateTask(User $user, ProjectTask $task): bool
    {
        // Super Admin / Manager can update any task
        if ($user->isManager()) return true;
        
        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) return true;
        
        // Project Manager can update any task
        if ($this->isUserProjectManager($user)) return true;
        
        // Regular employees cannot update tasks (only view)
        return false;
    }

    /**
     * Check if user can manage expenses
     */
    public function canUserManageExpenses(User $user): bool
    {
        // Super Admin / Manager can manage expenses
        if ($user->isManager()) return true;
        
        // Check if user has can_edit permission in this project
        $projectPerson = $this->projectPeople()->where('user_id', $user->id)->first();
        if ($projectPerson && $projectPerson->can_edit) return true;
        
        // Project Manager can manage expenses
        return $this->isUserProjectManager($user);
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
    public function isPlanning(): bool { return $this->status === 'planning'; }
    public function isQuoted(): bool { return $this->status === 'quoted'; }
    public function isAwarded(): bool { return $this->status === 'awarded'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isPaused(): bool { return $this->status === 'paused'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isLost(): bool { return $this->status === 'lost'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    
    // Legacy aliases
    public function isActive(): bool { return $this->status === 'in_progress'; }
    public function isOnHold(): bool { return $this->status === 'paused'; }
    

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
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
        return ucfirst(str_replace('_', ' ', $this->status));
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
        if (!$this->end_date) return null;
        return now()->diffInDays($this->end_date, false);
    }

    public function isOverdue(): bool
    {
        return $this->end_date && now()->greaterThan($this->end_date) && !$this->isCompleted();
    }
}
