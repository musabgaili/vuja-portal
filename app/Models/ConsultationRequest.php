<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ConsultationRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'category',
        'title',
        'description',
        'specific_questions',
        'status',
        'assigned_to',
        'meeting_scheduled_at',
        'meeting_link',
        'meeting_notes',
    ];

    protected $casts = [
        'meeting_scheduled_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'category'])
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
    public function isFiltered(): bool { return $this->status === 'filtered'; }
    public function isAssigned(): bool { return $this->status === 'assigned'; }
    public function isMeetingScheduled(): bool { return $this->status === 'meeting_scheduled'; }
    public function isMeetingSent(): bool { return $this->status === 'meeting_sent'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'submitted' => 'info',
            'filtered' => 'warning',
            'assigned' => 'primary',
            'meeting_scheduled' => 'success',
            'meeting_sent' => 'success',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'submitted' => 'Submitted',
            'filtered' => 'Filtered',
            'assigned' => 'Assigned',
            'meeting_scheduled' => 'Meeting Scheduled',
            'meeting_sent' => 'Meeting Invitation Sent',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }
}
