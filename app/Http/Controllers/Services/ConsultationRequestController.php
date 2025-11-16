<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

use App\Models\ConsultationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'Other'
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
        $user = Auth::user();
        
        if ($user->isClient() && $consultation->user_id !== $user->id) {
            abort(403);
        }

        $consultation->load(['user', 'assignedTo']);
        
        return view('consultations.show', compact('consultation'));
    }

    // MANAGER/EMPLOYEE SIDE
    
    public function managerIndex()
    {
        $user = Auth::user();
        
        if (!$user->isManager() && !$user->isEmployee()) {
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
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $consultation->load(['user', 'assignedTo']);
        $employees = User::where('role', 'employee')->get();

        return view('consultations.manager.show', compact('consultation', 'employees'));
    }

    public function assign(Request $request, ConsultationRequest $consultation)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $consultation->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'assigned',
        ]);

        return back()->with('success', 'Employee assigned successfully!');
    }

    public function sendMeetingInvite(Request $request, ConsultationRequest $consultation)
    {
        $user = Auth::user();
        
        if (!$user->isEmployee() && !$user->isManager()) {
            abort(403);
        }

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
        if (!$timeSlot->isAvailable()) {
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
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function complete(Request $request, ConsultationRequest $consultation)
    {
        $validated = $request->validate([
            'meeting_notes' => 'nullable|string',
        ]);

        $consultation->update([
            'meeting_notes' => $validated['meeting_notes'],
            'status' => 'completed',
        ]);

        return back()->with('success', 'Consultation marked as completed!');
    }

    // PRIVATE METHODS
    
    private function autoAssign(ConsultationRequest $consultation)
    {
        // Simple auto-assignment based on category
        // In production, this could be more sophisticated
        
        $employee = User::where('role', 'employee')
            ->whereHas('permissions', function($q) use ($consultation) {
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
