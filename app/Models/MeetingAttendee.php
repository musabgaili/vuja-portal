<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One invited internal colleague on a {@see Meeting}. Status is the colleague's
 * own response: 'invited' (recommendation pending their accept), 'accepted'
 * (confirmed, slot booked) or 'declined'.
 */
class MeetingAttendee extends Model
{
    protected $fillable = [
        'meeting_id',
        'user_id',
        'time_slot_id',
        'status',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function isInvited(): bool
    {
        return $this->status === 'invited';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }
}
