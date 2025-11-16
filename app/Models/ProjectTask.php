<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProjectTask extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id', 'milestone_id', 'title', 'description',
        'status', 'priority', 'assigned_to', 'created_by',
        'due_date', 'completed_at', 'estimated_hours', 'actual_hours',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ProjectComment::class, 'commentable');
    }

    public function isTodo(): bool { return $this->status === 'todo'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isInReview(): bool { return $this->status === 'review'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isBlocked(): bool { return $this->status === 'blocked'; }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'todo' => 'secondary',
            'in_progress' => 'primary',
            'review' => 'warning',
            'completed' => 'success',
            'blocked' => 'error',
            default => 'secondary'
        };
    }

    public function getPriorityBadgeColor(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'error',
            default => 'secondary'
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date && now()->greaterThan($this->due_date) && !$this->isCompleted();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'assigned_to', 'milestone_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Task created',
                'updated' => 'Task updated',
                'deleted' => 'Task deleted',
                default => $eventName
            });
    }
    
    public function tapActivity($activity, string $eventName)
    {
        $activity->subject_id = $this->project_id;
        $activity->subject_type = Project::class;
    }
}
