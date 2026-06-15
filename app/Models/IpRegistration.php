<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasServiceProject;

class IpRegistration extends Model
{
    use HasFactory, HasServiceProject, HasUuidRouteKey, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'ip_description',
        'ip_type',
        'supporting_documents',
        'status',
        'meeting_requested_at',
        'meeting_confirmed_at',
        'meeting_link',
        'registration_number',
        'filed_at',
        'registered_at',
        'assigned_to',
    ];

    protected $casts = [
        'supporting_documents' => 'array',
        'meeting_requested_at' => 'datetime',
        'meeting_confirmed_at' => 'datetime',
        'filed_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'registration_number'])
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
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isMeetingBooked(): bool
    {
        return $this->status === 'meeting_booked';
    }

    public function isMeetingConfirmed(): bool
    {
        return $this->status === 'meeting_confirmed';
    }

    public function isDocumentation(): bool
    {
        return $this->status === 'documentation';
    }

    public function isFiling(): bool
    {
        return $this->status === 'filing';
    }

    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'submitted' => 'info',
            'meeting_booked' => 'warning',
            'meeting_confirmed' => 'success',
            'documentation' => 'primary',
            'filing' => 'warning',
            'registered' => 'success',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'submitted' => __('portal.ip.status_submitted'),
            'meeting_booked' => __('portal.ip.status_meeting_booked'),
            'meeting_confirmed' => __('portal.ip.status_meeting_confirmed'),
            'documentation' => __('portal.ip.status_documentation'),
            'filing' => __('portal.ip.status_filing'),
            'registered' => __('portal.ip.status_registered'),
            'completed' => __('portal.ip.status_completed'),
            'cancelled' => __('portal.ip.status_cancelled'),
            default => str_replace('_', ' ', $this->status)
        };
    }
}
