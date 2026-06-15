<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ConsultationRequestController extends Controller
{
    // CLIENT SIDE

    public function create()
    {
        $categories = [
            'Business Strategy',
            'Technology Consulting',
            'Marketing & Branding',
            'Legal Advice',
            'Financial Planning',
            'Product Development',
            'Other',
        ];

        return view('consultations.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'specific_questions' => 'nullable|string',
        ]);

        $consultation = ConsultationRequest::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status' => 'submitted',
        ]);

        // Auto-filter and assign based on category
        $this->autoAssign($consultation);

        return redirect()->route('consultations.show', $consultation)
            ->with('success', 'Consultation request submitted successfully!');
    }

    public function show(ConsultationRequest $consultation)
    {
        $this->authorize('view', $consultation);

        $consultation->load(['user', 'assignedTo']);

        return view('consultations.show', compact('consultation'));
    }

    // MANAGER/EMPLOYEE SIDE

    public function managerIndex()
    {
        $user = Auth::user();

        if (! $user->isManager() && ! $user->isEmployee() && ! $user->isProjectManager()) {
            abort(403);
        }

        $query = ConsultationRequest::with(['user', 'assignedTo']);

        if ($user->isEmployee()) {
            $query->where('assigned_to', $user->id);
        }

        $consultations = $query->latest()->paginate(15);

        return view('consultations.manager.index', compact('consultations'));
    }

    public function managerShow(ConsultationRequest $consultation)
    {
        $user = Auth::user();

        $this->authorize('manage', $consultation);

        $consultation->load(['user', 'assignedTo']);
        $employees = User::where('role', 'employee')->get();

        return view('consultations.manager.show', compact('consultation', 'employees'));
    }

    public function assign(Request $request, ConsultationRequest $consultation)
    {
        $this->authorize('manage', $consultation);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $consultation->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'assigned',
        ]);

        return back()->with('success', 'Employee assigned successfully!');
    }

    /**
     * Assign an employee AND schedule the consultation meeting in one step.
     *
     * The manager/PM either picks one of the employee's existing available
     * slots, or supplies a date/time which auto-creates the availability for
     * that employee. Either way the meeting is booked and the client is notified.
     */
    public function assignAndSchedule(Request $request, ConsultationRequest $consultation)
    {
        $user = Auth::user();

        if (! $user->isManager() && ! $user->isProjectManager()) {
            abort(403);
        }
        $this->authorize('manage', $consultation);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'mode' => 'required|in:existing,new',
            'time_slot_id' => 'required_if:mode,existing|nullable|exists:time_slots,id',
            'date' => 'required_if:mode,new|nullable|date|after_or_equal:today',
            'start_time' => 'required_if:mode,new|nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'meeting_link' => 'nullable|url',
        ]);

        $employeeId = (int) $validated['assigned_to'];

        // Resolve the time slot: an existing available one, or a freshly created availability.
        if ($validated['mode'] === 'existing') {
            $timeSlot = \App\Models\TimeSlot::findOrFail($validated['time_slot_id']);

            if ($timeSlot->user_id !== $employeeId) {
                return back()->withErrors(['error' => 'That time slot does not belong to the selected employee.'])->withInput();
            }
        } else {
            $startTime = $validated['start_time'];
            $endTime = $validated['end_time'] ?: \Carbon\Carbon::parse($startTime)->addMinutes(60)->format('H:i');

            // Reuse an identical slot if the employee already created it, else create the availability.
            $timeSlot = \App\Models\TimeSlot::firstOrCreate(
                [
                    'user_id' => $employeeId,
                    'date' => $validated['date'],
                    'start_time' => $startTime,
                ],
                [
                    'end_time' => $endTime,
                    'status' => 'available',
                    'notes' => 'Auto-created for consultation #'.$consultation->id,
                ],
            );
        }

        if (! $timeSlot->isAvailable()) {
            return back()->withErrors(['error' => 'That time slot is no longer available. Please pick another time.'])->withInput();
        }

        // Assign the employee.
        $consultation->update([
            'assigned_to' => $employeeId,
            'status' => 'assigned',
        ]);

        // Book the meeting (creates the Meeting record + marks the slot booked).
        // Cap the meeting at the slot length so a short slot doesn't fail validation.
        $slotMinutes = (int) \Carbon\Carbon::parse($timeSlot->start_time)->diffInMinutes(\Carbon\Carbon::parse($timeSlot->end_time));
        $meetingService = app(\App\Services\ServiceRequests\MeetingService::class);

        try {
            $meeting = $meetingService->bookMeeting($consultation->user, $timeSlot, [
                'title' => $consultation->title,
                'description' => $consultation->description,
                'duration_minutes' => min(60, $slotMinutes ?: 60),
            ]);

            // Link the meeting back to this consultation for traceability.
            if (\Illuminate\Support\Facades\Schema::hasColumn('meetings', 'bookable_type')) {
                $meeting->update([
                    'bookable_type' => ConsultationRequest::class,
                    'bookable_id' => $consultation->id,
                ]);
            }

            $consultation->update([
                'meeting_scheduled_at' => $meeting->scheduled_at,
                'meeting_link' => $validated['meeting_link'] ?? $consultation->meeting_link,
                'status' => 'meeting_sent',
            ]);

            return back()->with('success', 'Employee assigned and the consultation meeting was scheduled.');
        } catch (\Exception $e) {
            Log::error('Consultation assign-and-schedule failed.', [
                'consultation_id' => $consultation->id,
                'employee_id' => $employeeId,
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => 'We could not schedule the consultation meeting right now. Please try again.'])->withInput();
        }
    }

    public function sendMeetingInvite(Request $request, ConsultationRequest $consultation)
    {
        $user = Auth::user();

        if (! $user->isEmployee() && ! $user->isManager() && ! $user->isProjectManager()) {
            abort(403);
        }
        $this->authorize('manage', $consultation);

        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'meeting_link' => 'nullable|url',
        ]);

        // Get the selected time slot
        $timeSlot = \App\Models\TimeSlot::findOrFail($validated['time_slot_id']);

        // Verify the time slot belongs to the assigned employee
        if ($timeSlot->user_id !== $consultation->assigned_to) {
            return back()->withErrors(['error' => 'Selected time slot does not belong to the assigned employee.']);
        }

        // Check if slot is still available
        if (! $timeSlot->isAvailable()) {
            return back()->withErrors(['error' => 'Selected time slot is no longer available.']);
        }

        // Create the meeting
        $meetingService = app(\App\Services\ServiceRequests\MeetingService::class);

        try {
            $meeting = $meetingService->bookMeeting($consultation->user, $timeSlot, [
                'title' => $consultation->title,
                'description' => $consultation->description,
                'duration_minutes' => 60, // Default 60 minutes for consultations
            ]);

            // Update consultation with meeting details
            $consultation->update([
                'meeting_scheduled_at' => $meeting->scheduled_at,
                'meeting_link' => $validated['meeting_link'] ?? null,
                'status' => 'meeting_sent',
            ]);

            return back()->with('success', 'Meeting scheduled and invitation sent to client!');

        } catch (\Exception $e) {
            Log::error('Consultation meeting invitation failed.', [
                'consultation_id' => $consultation->id,
                'time_slot_id' => $timeSlot->id,
                'assigned_to' => $consultation->assigned_to,
                'exception' => $e,
            ]);

            return back()->withErrors([
                'error' => 'We could not schedule the consultation meeting right now. Please try again.',
            ]);
        }
    }

    public function complete(Request $request, ConsultationRequest $consultation)
    {
        $this->authorize('manage', $consultation);

        $validated = $request->validate([
            'meeting_notes' => 'nullable|string',
        ]);

        $consultation->update([
            'meeting_notes' => $validated['meeting_notes'],
            'status' => 'completed',
        ]);

        return back()->with('success', 'Consultation marked as completed!');
    }

    /** Turn a completed consultation into a project + funnel entry. */
    public function convertToProject(ConsultationRequest $consultation)
    {
        $this->authorize('manage', $consultation);

        if ($consultation->status !== 'completed') {
            return back()->withErrors(['error' => 'Only completed consultations can be converted to projects.']);
        }

        $already = $consultation->isConvertedToProject();

        $project = app(\App\Services\ServiceProjectConverter::class)->convert($consultation, [
            'title' => $consultation->title,
            'description' => $consultation->description,
            'scope' => 'Category: '.$consultation->category,
            'client_id' => $consultation->user_id,
            'project_manager_id' => $consultation->assigned_to,
            'source_label' => 'Consultation',
        ]);

        return redirect()->route('projects.manager.show', $project)
            ->with($already ? 'info' : 'success', $already ? 'Project already exists!' : 'Project created from consultation — added to the sales funnel.');
    }

    // PRIVATE METHODS

    private function autoAssign(ConsultationRequest $consultation)
    {
        // Simple auto-assignment based on category
        // In production, this could be more sophisticated

        $employee = User::where('role', 'employee')
            ->whereHas('permissions', function ($q) {
                // Could check for category-specific permissions
            })
            ->inRandomOrder()
            ->first();

        if ($employee) {
            $consultation->update([
                'assigned_to' => $employee->id,
                'status' => 'assigned',
            ]);
        } else {
            $consultation->update(['status' => 'filtered']);
        }
    }
}
