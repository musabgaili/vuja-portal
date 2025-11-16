<?php

namespace App\Services\ServiceRequests;

use App\Models\Meeting;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;

class MeetingService
{
    /**
     * Book a meeting for a client
     */
    public function bookMeeting(User $client, TimeSlot $timeSlot, array $data): Meeting
    {
        // Double-check availability before booking
        if (!$timeSlot->isAvailable()) {
            throw new \Exception('Time slot is no longer available. It may have been booked by someone else.');
        }

        // Check for any overlapping meetings
        if ($timeSlot->hasOverlappingMeetings()) {
            throw new \Exception('This time slot conflicts with an existing meeting.');
        }

        // Validate meeting duration doesn't exceed slot duration
        $slotDuration = Carbon::parse($timeSlot->start_time)->diffInMinutes(Carbon::parse($timeSlot->end_time));
        $meetingDuration = (int)($data['duration_minutes'] ?? 60);
        
        if ($meetingDuration > $slotDuration) {
            throw new \Exception("Meeting duration ({$meetingDuration} minutes) cannot exceed slot duration ({$slotDuration} minutes).");
        }

        $scheduledAt = Carbon::parse($timeSlot->date->format('Y-m-d') . ' ' . $timeSlot->start_time);

        $meeting = Meeting::create([
            'time_slot_id' => $timeSlot->id,
            'client_id' => $client->id,
            'team_member_id' => $timeSlot->user_id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $meetingDuration,
            'status' => 'scheduled',
        ]);

        // Mark slot as booked
        $timeSlot->update(['status' => 'booked']);

        return $meeting;
    }

    /**
     * Get meetings for a user (client or team member)
     */
    public function getUserMeetings(User $user)
    {
        if ($user->isClient()) {
            return Meeting::where('client_id', $user->id)
                ->with(['teamMember', 'timeSlot'])
                ->latest('scheduled_at')
                ->paginate(15);
        } else {
            return Meeting::where('team_member_id', $user->id)
                ->with(['client', 'timeSlot'])
                ->latest('scheduled_at')
                ->paginate(15);
        }
    }

    /**
     * Confirm a meeting (team member)
     */
    public function confirmMeeting(Meeting $meeting, ?string $meetingLink = null): void
    {
        $meeting->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'meeting_link' => $meetingLink ?? $meeting->meeting_link,
        ]);
    }

    /**
     * Cancel a meeting
     */
    public function cancelMeeting(Meeting $meeting): void
    {
        $meeting->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Free up the time slot
        $meeting->timeSlot->update(['status' => 'available']);
    }

    /**
     * Complete a meeting
     */
    public function completeMeeting(Meeting $meeting, ?string $notes = null): void
    {
        $meeting->update([
            'status' => 'completed',
            'completed_at' => now(),
            'meeting_notes' => $notes ?? $meeting->meeting_notes,
        ]);
    }
}

