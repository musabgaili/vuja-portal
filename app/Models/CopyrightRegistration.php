<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CopyrightRegistration extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'work_description',
        'work_type',
        'work_files',
        'status',
        'meeting_requested_at',
        'meeting_confirmed_at',
        'meeting_link',
        'copyright_number',
        'filed_at',
        'registered_at',
        'assigned_to',
    ];

    protected $casts = [
        'work_files' => 'array',
        'meeting_requested_at' => 'datetime',
        'meeting_confirmed_at' => 'datetime',
        'filed_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'copyright_number'])
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

    // Status helper methods
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isMeetingBooked(): bool { return $this->status === 'meeting_booked'; }
    public function isMeetingConfirmed(): bool { return $this->status === 'meeting_confirmed'; }
    public function isFiling(): bool { return $this->status === 'filing'; }
    public function isRegistered(): bool { return $this->status === 'registered'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'submitted' => 'info',
            'meeting_booked' => 'warning',
            'meeting_confirmed' => 'success',
            'filing' => 'warning',
            'registered' => 'success',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'submitted' => 'Submitted',
            'meeting_booked' => 'Meeting Booked',
            'meeting_confirmed' => 'Meeting Confirmed',
            'filing' => 'Filing',
            'registered' => 'Registered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status)
        };
    }
}
