<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ResearchRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'research_topic',
        'research_details',
        'relevant_links',
        'uploaded_files',
        'status',
        'nda_signed_at',
        'sla_signed_at',
        'nda_document',
        'sla_document',
        'meeting_scheduled_at',
        'meeting_link',
        'research_findings',
        'assigned_to',
    ];

    protected $casts = [
        'uploaded_files' => 'array',
        'nda_signed_at' => 'datetime',
        'sla_signed_at' => 'datetime',
        'meeting_scheduled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isNdaPending(): bool { return $this->status === 'nda_pending'; }
    public function isNdaSigned(): bool { return $this->status === 'nda_signed'; }
    public function isDetailsProvided(): bool { return $this->status === 'details_provided'; }
    public function isMeetingScheduled(): bool { return $this->status === 'meeting_scheduled'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'submitted' => 'info',
            'nda_pending' => 'warning',
            'nda_signed' => 'success',
            'details_provided' => 'info',
            'meeting_scheduled' => 'success',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'submitted' => 'Submitted',
            'nda_pending' => 'NDA Pending',
            'nda_signed' => 'NDA Signed',
            'details_provided' => 'Details Provided',
            'meeting_scheduled' => 'Meeting Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }
}
