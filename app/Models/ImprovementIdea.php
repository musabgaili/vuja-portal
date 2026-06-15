<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * An internal "portal improvement" suggestion submitted by a client, employee
 * or project manager. When a manager approves it, the submitter earns the
 * highest engagement (Impact Points) award — see ImprovementIdeaController.
 */
class ImprovementIdea extends Model
{
    use HasFactory, HasUuidRouteKey, LogsActivity;

    protected $fillable = [
        'uuid', 'user_id', 'title', 'category', 'description', 'technology_used',
        'benefit', 'status', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'reviewed_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isImplemented(): bool
    {
        return $this->status === 'implemented';
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'submitted' => 'info',
            'approved' => 'success',
            'implemented' => 'primary',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return __('portal.improvement_ideas.status.'.$this->status);
    }
}
