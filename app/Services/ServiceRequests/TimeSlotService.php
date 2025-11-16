<?php

namespace App\Services\ServiceRequests;

use App\Models\TimeSlot;
use App\Models\User;
use Carbon\Carbon;

class TimeSlotService
{
    /**
     * Get time slots for a specific user
     */
    public function getUserTimeSlots(User $user, bool $includePast = false)
    {
        $query = TimeSlot::with(['user', 'meeting.client'])
            ->where('user_id', $user->id);

        if (!$includePast) {
            $query->where('date', '>=', today());
        }

        return $query->orderBy('date')
            ->orderBy('start_time')
            ->paginate(20);
    }

    /**
     * Get all team time slots (managers only)
     */
    public function getAllTeamTimeSlots(bool $includePast = false)
    {
        $query = TimeSlot::with(['user', 'meeting.client']);

        if (!$includePast) {
            $query->where('date', '>=', today());
        }

        return $query->orderBy('date')
            ->orderBy('start_time')
            ->paginate(30);
    }

    /**
     * Get available slots for client booking
     */
    public function getAvailableSlotsForBooking(?int $teamMemberId = null)
    {
        $query = TimeSlot::with(['user'])
            ->where('status', 'available')
            ->where('date', '>=', today())
            ->whereDoesntHave('meeting');

        if ($teamMemberId) {
            $query->where('user_id', $teamMemberId);
        }

        $slots = $query->orderBy('date')
            ->orderBy('start_time')
            ->get();

        // Filter out slots that have overlapping meetings
        $availableSlots = $slots->filter(function($slot) {
            return $slot->isAvailable();
        });

        // Convert back to paginated collection
        $perPage = 30;
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $paginatedSlots = $availableSlots->slice($offset, $perPage);
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedSlots,
            $availableSlots->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    /**
     * Create bulk time slots based on selected days and times
     */
    public function createBulkTimeSlots(User $user, array $data): int
    {
        $slotsCreated = 0;
        $startDate = Carbon::parse($data['start_date']);
        $endDate = $startDate->copy()->addWeeks((int)$data['weeks']);
        
        // Map day names to Carbon day numbers
        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        // Get selected day numbers
        $selectedDays = array_map(fn($day) => $dayMap[$day], $data['days']);

        // Loop through each date in the range
        for ($date = $startDate->copy(); $date->lessThan($endDate); $date->addDay()) {
            // Check if this day is selected
            if (!in_array($date->dayOfWeek, $selectedDays)) {
                continue;
            }

            // Create a slot for each selected time on this day
            foreach ($data['time_slots'] as $startTime) {
                $endTime = Carbon::parse($startTime)
                    ->addMinutes((int)$data['slot_duration'])
                    ->format('H:i');

                // Check if slot already exists (avoid duplicates)
                $exists = TimeSlot::where('user_id', $user->id)
                    ->where('date', $date->format('Y-m-d'))
                    ->where('start_time', $startTime)
                    ->exists();

                if (!$exists) {
                    TimeSlot::create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'is_recurring' => true,
                        'recurring_pattern' => 'weekly',
                        'notes' => $data['notes'] ?? null,
                    ]);
                    
                    $slotsCreated++;
                }
            }
        }

        return $slotsCreated;
    }

    /**
     * Delete a time slot (only if not booked)
     */
    public function deleteTimeSlot(TimeSlot $timeSlot): bool
    {
        if ($timeSlot->isBooked()) {
            return false;
        }

        return $timeSlot->delete();
    }

    /**
     * Get employee availability summary for managers
     */
    public function getEmployeeAvailabilitySummary(User $employee, int $days = 7)
    {
        $startDate = today();
        $endDate = today()->addDays($days);
        
        $slots = TimeSlot::where('user_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['meeting.client'])
            ->get();
            
        $summary = [
            'total_slots' => $slots->count(),
            'available_slots' => $slots->where('status', 'available')->whereDoesntHave('meeting')->count(),
            'booked_slots' => $slots->where('status', 'booked')->count(),
            'blocked_slots' => $slots->where('status', 'blocked')->count(),
            'meetings_count' => $slots->whereNotNull('meeting')->count(),
            'availability_percentage' => 0,
        ];
        
        if ($summary['total_slots'] > 0) {
            $summary['availability_percentage'] = round(
                ($summary['available_slots'] / $summary['total_slots']) * 100, 2
            );
        }
        
        return $summary;
    }
}

