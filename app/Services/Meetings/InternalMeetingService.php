<?php

namespace App\Services\Meetings;

use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Books internal, multi-attendee meetings at a chosen date/time and decides, per
 * invited colleague, whether to:
 *   - BOOK their existing available slot (the time falls inside their availability),
 *   - INVITE them (a recommendation they must accept — they have slots, just not at
 *     this time), or
 *   - FORCE-BOOK by creating a slot directly (the colleague has no availability at
 *     all AND the organiser is a manager/PM who may manage others' calendars; the
 *     organiser themselves is always force-booked).
 * A regular employee booking a slotless colleague falls back to an INVITE.
 */
class InternalMeetingService
{
    /**
     * @param  array  $data  title, description, date (Y-m-d), start_time (H:i),
     *                       duration_minutes (int), attendee_ids (int[])
     * @return array{meeting: Meeting, results: array<int, array{name: string, outcome: string}>}
     */
    public function book(User $organizer, array $data): array
    {
        $date = $data['date'];
        $start = Carbon::parse($data['start_time'])->format('H:i:s');
        $duration = (int) $data['duration_minutes'];
        $end = Carbon::parse($data['start_time'])->addMinutes($duration)->format('H:i:s');
        $scheduledAt = Carbon::parse($date.' '.$start);

        if ($scheduledAt->isPast()) {
            throw new \RuntimeException(__('portal.meetings.past_time'));
        }

        $attendeeIds = collect($data['attendee_ids'])
            ->map(fn ($i) => (int) $i)->filter()->unique()->values();

        if ($attendeeIds->isEmpty()) {
            throw new \RuntimeException(__('portal.meetings.no_attendees'));
        }

        $canForce = $organizer->isManager() || $organizer->isProjectManager();

        return DB::transaction(function () use ($organizer, $attendeeIds, $date, $start, $end, $duration, $scheduledAt, $data, $canForce) {
            $primaryId = (int) $attendeeIds->first();

            // 1) Decide + reserve each attendee's slot BEFORE the meeting exists, so
            //    the host's own meeting can't register as a conflict against itself.
            $decisions = [];
            foreach ($attendeeIds as $uid) {
                $uid = (int) $uid;
                $user = User::where('type', 'internal')->find($uid);
                if (! $user) {
                    continue;
                }
                $isSelf = $uid === $organizer->id;

                if ($slot = $this->coveringSlot($uid, $date, $start, $end)) {
                    $slot->update(['status' => 'booked']);
                    $decisions[$uid] = ['name' => $user->name, 'outcome' => 'booked', 'slot_id' => $slot->id];

                    continue;
                }

                $hasSlots = $this->hasAnyAvailableSlot($uid);

                if ($isSelf || ($canForce && ! $hasSlots)) {
                    $newSlot = $this->createBookedSlot($uid, $date, $start, $end);
                    $decisions[$uid] = ['name' => $user->name, 'outcome' => $isSelf ? 'booked' : 'force_booked', 'slot_id' => $newSlot->id];

                    continue;
                }

                $decisions[$uid] = ['name' => $user->name, 'outcome' => 'invited', 'slot_id' => null];
            }

            if (empty($decisions)) {
                throw new \RuntimeException(__('portal.meetings.no_attendees'));
            }

            // 2) Create the meeting, adopting the primary host's slot if confirmed.
            $meeting = Meeting::create([
                'time_slot_id' => $decisions[$primaryId]['slot_id'] ?? null,
                'client_id' => $organizer->id,        // organiser / booker
                'team_member_id' => $primaryId,        // primary host (first attendee)
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $duration,
                'status' => 'scheduled',
            ]);

            // 3) Record attendee rows.
            $results = [];
            foreach ($decisions as $uid => $d) {
                $status = $d['outcome'] === 'invited' ? 'invited' : 'accepted';
                $this->upsertAttendee($meeting, $uid, $status, $d['slot_id']);
                $results[$uid] = ['name' => $d['name'], 'outcome' => $d['outcome']];
            }

            return ['meeting' => $meeting, 'results' => $results];
        });
    }

    /**
     * The invited colleague accepts: confirm them and consume (or create) a slot
     * at the meeting time. Idempotent — re-accepting an accepted invite is a no-op.
     */
    public function accept(MeetingAttendee $attendee): void
    {
        if ($attendee->status === 'accepted') {
            return;
        }

        $meeting = $attendee->meeting;
        $date = $meeting->scheduled_at->format('Y-m-d');
        $start = $meeting->scheduled_at->format('H:i:s');
        $end = $meeting->scheduled_at->copy()->addMinutes($meeting->duration_minutes)->format('H:i:s');

        DB::transaction(function () use ($attendee, $meeting, $date, $start, $end) {
            // Ignore this very meeting when checking for conflicts.
            $slot = $this->coveringSlot($attendee->user_id, $date, $start, $end, $meeting->id)
                ?? $this->createBookedSlot($attendee->user_id, $date, $start, $end);

            if ($slot->status !== 'booked') {
                $slot->update(['status' => 'booked']);
            }

            $attendee->update([
                'status' => 'accepted',
                'time_slot_id' => $slot->id,
                'responded_at' => now(),
            ]);

            // If the host accepted and the meeting had no confirmed slot yet, adopt it.
            if (! $meeting->time_slot_id && (int) $meeting->team_member_id === (int) $attendee->user_id) {
                $meeting->update(['time_slot_id' => $slot->id]);
            }
        });
    }

    /** The invited colleague declines. Their slot (if any) is freed. */
    public function decline(MeetingAttendee $attendee): void
    {
        DB::transaction(function () use ($attendee) {
            if ($attendee->time_slot_id && $slot = TimeSlot::find($attendee->time_slot_id)) {
                if (! $slot->meeting) {
                    $slot->update(['status' => 'available']);
                }
            }
            $attendee->update([
                'status' => 'declined',
                'time_slot_id' => null,
                'responded_at' => now(),
            ]);
        });
    }

    /**
     * An available, un-booked, not-past slot of this user that fully covers the
     * window — provided the user has no other meeting clashing with it.
     */
    private function coveringSlot(int $userId, string $date, string $start, string $end, ?int $ignoreMeetingId = null): ?TimeSlot
    {
        if ($this->userHasConflict($userId, $date, $start, $end, $ignoreMeetingId)) {
            return null;
        }

        return TimeSlot::where('user_id', $userId)
            ->whereDate('date', $date)
            ->where('status', 'available')
            ->whereDoesntHave('meeting')
            ->where('start_time', '<=', $start)
            ->where('end_time', '>=', $end)
            ->orderBy('start_time')
            ->get()
            ->first(fn (TimeSlot $s) => ! $s->isPast());
    }

    /** Does the user already have a (non-cancelled) meeting overlapping this window? */
    private function userHasConflict(int $userId, string $date, string $start, string $end, ?int $ignoreMeetingId = null): bool
    {
        $winStart = Carbon::parse($date.' '.$start);
        $winEnd = Carbon::parse($date.' '.$end);

        return Meeting::query()
            ->where('status', '!=', 'cancelled')
            ->when($ignoreMeetingId, fn ($q) => $q->where('id', '!=', $ignoreMeetingId))
            ->whereDate('scheduled_at', $date)
            ->where(function ($q) use ($userId) {
                $q->where('team_member_id', $userId)
                    ->orWhereHas('acceptedAttendees', fn ($a) => $a->where('user_id', $userId));
            })
            ->get()
            ->contains(function (Meeting $m) use ($winStart, $winEnd) {
                $ms = $m->scheduled_at;
                $me = $m->scheduled_at->copy()->addMinutes($m->duration_minutes);

                return $ms < $winEnd && $me > $winStart;
            });
    }

    private function hasAnyAvailableSlot(int $userId): bool
    {
        return TimeSlot::where('user_id', $userId)
            ->where('status', 'available')
            ->where('date', '>=', today()->toDateString())
            ->whereDoesntHave('meeting')
            ->exists();
    }

    /** Create (or reuse) a slot at the exact window and mark it booked. */
    private function createBookedSlot(int $userId, string $date, string $start, string $end): TimeSlot
    {
        $slot = TimeSlot::firstOrNew([
            'user_id' => $userId,
            'date' => $date,
            'start_time' => $start,
        ]);
        $slot->end_time = $end;
        $slot->status = 'booked';
        $slot->save();

        return $slot;
    }

    private function upsertAttendee(Meeting $meeting, int $userId, string $status, ?int $slotId): void
    {
        MeetingAttendee::updateOrCreate(
            ['meeting_id' => $meeting->id, 'user_id' => $userId],
            ['status' => $status, 'time_slot_id' => $slotId, 'responded_at' => $status === 'invited' ? null : now()]
        );
    }
}
