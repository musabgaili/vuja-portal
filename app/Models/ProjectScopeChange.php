<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProjectScopeChange extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id', 'requested_by', 'title', 'description', 'justification', 'budget_delta',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes',
        'client_signature', 'client_signed_at', 'client_ip', 'applied_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'client_signed_at' => 'datetime',
        'applied_at' => 'datetime',
        'budget_delta' => 'decimal:2',
    ];

    /** Approved, carries a budget change, and the client has not yet signed. */
    public function needsClientSignature(): bool
    {
        return $this->status === 'approved'
            && (float) $this->budget_delta !== 0.0
            && $this->client_signed_at === null;
    }

    public function isApplied(): bool
    {
        return $this->applied_at !== null;
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => __('portal.projects.scope_change_status.pending'),
            'approved' => __('portal.projects.scope_change_status.approved'),
            'rejected' => __('portal.projects.scope_change_status.rejected'),
            default => str_replace('_', ' ', $this->status)
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'review_notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => match ($eventName) {
                'created' => 'Scope change requested',
                'updated' => 'Scope change reviewed',
                'deleted' => 'Scope change cancelled',
                default => $eventName
            });
    }

    public function tapActivity($activity, string $eventName)
    {
        $activity->subject_id = $this->project_id;
        $activity->subject_type = Project::class;
    }
}
