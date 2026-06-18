<?php

namespace App\Http\Controllers;

use App\Models\TimeSlot;
use App\Models\User;
use App\Services\ServiceRequests\TimeSlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeSlotController extends Controller
{
    protected $timeSlotService;

    public function __construct(TimeSlotService $timeSlotService)
    {
        $this->middleware(['auth']);
        $this->timeSlotService = $timeSlotService;
    }

    /**
     * Show MY time slots (employee's own slots)
     */
    public function mySlots()
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        $slots = $this->timeSlotService->getUserTimeSlots($user);

        // All of the user's slots as calendar events (the list above is paginated;
        // the calendar needs the full set, coloured by status).
        $events = \App\Models\TimeSlot::where('user_id', $user->id)->with('meeting.client')->get()->map(function ($s) {
            $day = $s->date->format('Y-m-d');

            return [
                'id' => $s->id,
                'title' => $s->meeting?->client?->name ?: __('portal.time_slots.status.'.$s->status),
                'start' => $day.'T'.\Illuminate\Support\Carbon::parse($s->start_time)->format('H:i:s'),
                'end' => $day.'T'.\Illuminate\Support\Carbon::parse($s->end_time)->format('H:i:s'),
                'color' => $s->status === 'booked' ? '#0C7075' : ($s->status === 'blocked' ? '#ef4444' : '#10b981'),
                'extendedProps' => [
                    'status' => __('portal.time_slots.status.'.$s->status),
                    'bookedBy' => $s->meeting?->client?->name,
                    'meeting' => $s->meeting?->title,
                ],
            ];
        })->values();

        return view('time-slots.my-slots', compact('slots', 'events'));
    }

    /**
     * Show ALL team time slots (managers only - requires permission)
     */
    public function teamSlots()
    {
        $user = Auth::user();

        if (! $user->isManager()) {
            abort(403, 'You need manager role to view team slots.');
        }

        $slots = $this->timeSlotService->getAllTeamTimeSlots();
        $teamMembers = User::where('type', 'internal')->orderBy('name')->get();

        // Upcoming available-slot count per member so the roster can show
        // everyone — including those with no availability yet.
        $availableCounts = TimeSlot::where('status', 'available')
            ->whereDate('date', '>=', today())
            ->selectRaw('user_id, COUNT(*) as c')
            ->groupBy('user_id')
            ->pluck('c', 'user_id');

        return view('time-slots.team-slots', compact('slots', 'teamMembers', 'availableCounts'));
    }

    /**
     * Manager quick-add: create a single availability slot for another employee.
     */
    public function storeForEmployee(Request $request)
    {
        $user = Auth::user();

        if (! $user->isManager()) {
            abort(403, 'You need manager role to add availability for others.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'notes' => 'nullable|string',
        ]);

        // Only internal team members can hold availability.
        $target = User::where('id', $validated['user_id'])->where('type', 'internal')->firstOrFail();

        TimeSlot::firstOrCreate(
            [
                'user_id' => $target->id,
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
            ],
            [
                'end_time' => $validated['end_time'],
                'status' => 'available',
                'notes' => $validated['notes'] ?? null,
            ],
        );

        return back()->with('success', "Availability added for {$target->name}.");
    }

    /**
     * Show form to create time slots
     */
    public function create()
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        return view('time-slots.create');
    }

    /**
     * Store new time slot(s) - Uses service layer
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        $validated = $request->validate([
            'days' => 'required|array|min:1',
            'days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'time_slots' => 'required|array|min:1',
            'time_slots.*' => 'date_format:H:i',
            'start_date' => 'required|date|after_or_equal:today',
            'weeks' => 'required|integer|min:1|max:8',
            'slot_duration' => 'required|integer|in:30,60,90,120',
            'notes' => 'nullable|string',
        ]);

        $slotsCreated = $this->timeSlotService->createBulkTimeSlots($user, $validated);

        return redirect()->route('time-slots.my-slots')
            ->with('success', "$slotsCreated time slots created successfully!");
    }

    /**
     * Delete a time slot
     */
    public function destroy(TimeSlot $timeSlot)
    {
        $user = Auth::user();

        // Only slot owner or manager can delete
        if ($timeSlot->user_id !== $user->id && ! $user->isManager()) {
            abort(403);
        }

        $deleted = $this->timeSlotService->deleteTimeSlot($timeSlot);

        if (! $deleted) {
            return back()->withErrors(['error' => 'Cannot delete booked time slot!']);
        }

        return back()->with('success', 'Time slot deleted successfully!');
    }

    /**
     * Block/unblock a time slot
     */
    public function toggleBlock(TimeSlot $timeSlot)
    {
        $user = Auth::user();

        if ($timeSlot->user_id !== $user->id && ! $user->isManager()) {
            abort(403);
        }

        $toggled = $this->timeSlotService->toggleBlockStatus($timeSlot);

        if (! $toggled) {
            return back()->withErrors(['error' => 'Cannot block booked time slot!']);
        }

        return back()->with('success', 'Time slot updated!');
    }

    /**
     * Get available time slots for a specific employee (API endpoint)
     */
    public function getAvailableSlots($employeeId)
    {
        $user = Auth::user();

        if (! $user->isInternal()) {
            abort(403);
        }

        // Verify employee exists and is internal
        $employee = User::where('id', $employeeId)
            ->where('type', 'internal')
            ->first();

        if (! $employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Get available slots for the employee
        $slots = $this->timeSlotService->getAvailableSlotsForBooking($employeeId);

        // Format slots for JSON response
        $formattedSlots = $slots->map(function ($slot) {
            $startTime = \Carbon\Carbon::parse($slot->start_time);
            $endTime = \Carbon\Carbon::parse($slot->end_time);
            $duration = $startTime->diffInMinutes($endTime);

            return [
                'id' => $slot->id,
                'date' => $slot->date->format('Y-m-d'),
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'duration' => $duration,
                'employee_name' => $slot->user->name,
            ];
        });

        return response()->json([
            'slots' => $formattedSlots,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
            ],
        ]);
    }
}
