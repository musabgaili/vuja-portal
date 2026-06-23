<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasServiceProject;
use App\Models\Concerns\HasServiceWorkPanel;

class ResearchRequest extends Model
{
    use HasAutoTranslations;

    protected array $translatable = ['title', 'research_topic', 'research_details'];

    use HasFactory, HasServiceProject, HasServiceWorkPanel, HasUuidRouteKey, LogsActivity;

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

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isNdaPending(): bool
    {
        return $this->status === 'nda_pending';
    }

    public function isNdaSigned(): bool
    {
        return $this->status === 'nda_signed';
    }

    public function isDetailsProvided(): bool
    {
        return $this->status === 'details_provided';
    }

    public function isMeetingScheduled(): bool
    {
        return $this->status === 'meeting_scheduled';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
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
        return match ($this->status) {
            'submitted' => __('portal.research.status.submitted'),
            'nda_pending' => __('portal.research.status.nda_pending'),
            'nda_signed' => __('portal.research.status.nda_signed'),
            'details_provided' => __('portal.research.status.details_provided'),
            'meeting_scheduled' => __('portal.research.status.meeting_scheduled'),
            'in_progress' => __('portal.research.status.in_progress'),
            'completed' => __('portal.research.status.completed'),
            'cancelled' => __('portal.research.status.cancelled'),
            default => str_replace('_', ' ', $this->status)
        };
    }
}
