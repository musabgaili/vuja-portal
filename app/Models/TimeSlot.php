<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'status',
        'is_recurring',
        'recurring_pattern',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meeting(): HasOne
    {
        return $this->hasOne(Meeting::class);
    }

    public function isAvailable(): bool
    {
        // Check if slot is available and not booked
        if ($this->status !== 'available' || $this->meeting) {
            return false;
        }
        
        // Check if slot is in the past
        if ($this->isPast()) {
            return false;
        }
        
        // Additional check: ensure no overlapping meetings exist
        return !$this->hasOverlappingMeetings();
    }

    /**
     * Check if there are any overlapping meetings for this time slot
     */
    public function hasOverlappingMeetings(): bool
    {
        $slotStart = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->start_time);
        $slotEnd = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time);
        
        // Get all meetings for this user on the same date
        $meetings = Meeting::where('team_member_id', $this->user_id)
            ->where('status', '!=', 'cancelled')
            ->whereDate('scheduled_at', $this->date)
            ->get();
        
        // Check each meeting for overlap
        foreach ($meetings as $meeting) {
            $meetingStart = $meeting->scheduled_at;
            $meetingEnd = $meeting->scheduled_at->copy()->addMinutes($meeting->duration_minutes);
            
            // Check if meetings overlap
            if ($meetingStart < $slotEnd && $meetingEnd > $slotStart) {
                return true;
            }
        }
        
        return false;
    }

    public function isBooked(): bool
    {
        return $this->status === 'booked' || $this->meeting !== null;
    }

    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    public function isPast(): bool
    {
        $slotDateTime = Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time);
        return $slotDateTime->isPast();
    }

    public function getFormattedTimeRange(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A') . ' - ' . Carbon::parse($this->end_time)->format('g:i A');
    }

    public function getStatusBadgeColor(): string
    {
        if ($this->isPast()) return 'secondary';
        
        return match($this->status) {
            'available' => 'success',
            'booked' => 'danger',
            'blocked' => 'warning',
            default => 'secondary'
        };
    }
}
