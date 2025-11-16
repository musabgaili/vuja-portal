<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProjectMilestone extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id', 'title', 'description', 'milestone_order',
        'status', 'due_date', 'completed_at', 'completion_percentage',
        'client_approved', 'client_approved_at', 'approval_note',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'date',
        'client_approved' => 'boolean',
        'client_approved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'milestone_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ProjectComment::class, 'commentable');
    }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'secondary'
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'completion_percentage', 'due_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Milestone created',
                'updated' => 'Milestone updated',
                'deleted' => 'Milestone deleted',
                default => $eventName
            });
    }
    
    public function tapActivity($activity, string $eventName)
    {
        $activity->subject_id = $this->project_id;
        $activity->subject_type = Project::class;
    }
}
