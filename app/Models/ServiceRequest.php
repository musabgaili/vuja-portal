<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'service_request_type_id',
        'type',
        'title',
        'description',
        'status',
        'priority',
        'requirements',
        'budget_range',
        'timeline',
        'additional_info',
        'step_data',
        'current_step_id',
        'assigned_to',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'approved_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'step_data' => 'array',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'priority', 'assigned_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user that owns the service request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user assigned to this service request.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who reviewed this service request.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the service request type for this request.
     */
    public function serviceRequestType(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestType::class);
    }

    /**
     * Get the current step for this request.
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestStep::class, 'current_step_id');
    }

    /**
     * Check if the service request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the service request is in review.
     */
    public function isInReview(): bool
    {
        return $this->status === 'in_review';
    }

    /**
     * Check if the service request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the service request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if the service request is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the service request is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_review' => 'info',
            'approved' => 'success',
            'rejected' => 'error',
            'in_progress' => 'primary',
            'completed' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get the priority badge color.
     */
    public function getPriorityBadgeColor(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'error',
            'urgent' => 'error',
            default => 'secondary'
        };
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayName(): string
    {
        return match($this->type) {
            'idea' => 'Idea Generation',
            'consultation' => 'Consultation',
            'research' => 'Research & IP',
            'copyright' => 'Copyright Services',
            default => ucfirst($this->type)
        };
    }
}