<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Meeting extends Model
{
    use HasFactory, HasUuidRouteKey;

    protected $fillable = [
        'time_slot_id',
        'client_id',
        'team_member_id',
        'bookable_type',
        'bookable_id',
        'title',
        'description',
        'scheduled_at',
        'duration_minutes',
        'status',
        'meeting_link',
        'meeting_notes',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_member_id');
    }

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    /** All invited internal colleagues (beyond the organiser). */
    public function attendees(): HasMany
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    /** Attendees who have confirmed. */
    public function acceptedAttendees(): HasMany
    {
        return $this->attendees()->where('status', 'accepted');
    }

    /** Attendees still to respond to their invitation. */
    public function pendingAttendees(): HasMany
    {
        return $this->attendees()->where('status', 'invited');
    }

    /**
     * Distinct internal user ids who attended this meeting — the primary host
     * plus every accepted attendee. Used to credit "meeting attended" points.
     */
    public function attendeeUserIds(): array
    {
        return collect([$this->team_member_id])
            ->merge($this->acceptedAttendees()->pluck('user_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
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
            'scheduled' => 'info',
            'confirmed' => 'success',
            'completed' => 'secondary',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'scheduled' => __('portal.meetings.status.scheduled'),
            'confirmed' => __('portal.meetings.status.confirmed'),
            'completed' => __('portal.meetings.status.completed'),
            'cancelled' => __('portal.meetings.status.cancelled'),
            default => str_replace('_', ' ', $this->status)
        };
    }

    public function getEndTime()
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }
}
