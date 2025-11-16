<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\ServiceRequests\MeetingService;
use App\Services\ServiceRequests\TimeSlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    protected $meetingService;
    protected $timeSlotService;

    public function __construct(MeetingService $meetingService, TimeSlotService $timeSlotService)
    {
        $this->middleware(['auth', 'verified']);
        $this->meetingService = $meetingService;
        $this->timeSlotService = $timeSlotService;
    }

    /**
     * Show available time slots for booking (CLIENT VIEW)
     * Only shows slots for consultants assigned to the client's service requests
     */
    public function availableSlots(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            abort(403);
        }

        // Get all service requests with assigned consultants
        $assignedConsultantIds = collect();
        
        // Check consultation requests
        $consultations = \App\Models\ConsultationRequest::where('user_id', $user->id)
            ->whereNotNull('assigned_to')
            ->get();
        $assignedConsultantIds = $assignedConsultantIds->merge($consultations->pluck('assigned_to'));
        
        // Check research requests
        $research = \App\Models\ResearchRequest::where('user_id', $user->id)
            ->whereNotNull('assigned_to')
            ->get();
        $assignedConsultantIds = $assignedConsultantIds->merge($research->pluck('assigned_to'));
        
        // Check IP registration requests
        $ipRegistrations = \App\Models\IpRegistration::where('user_id', $user->id)
            ->whereNotNull('assigned_to')
            ->get();
        $assignedConsultantIds = $assignedConsultantIds->merge($ipRegistrations->pluck('assigned_to'));
        
        // Check copyright registration requests
        $copyrights = \App\Models\CopyrightRegistration::where('user_id', $user->id)
            ->whereNotNull('assigned_to')
            ->get();
        $assignedConsultantIds = $assignedConsultantIds->merge($copyrights->pluck('assigned_to'));

        // Get unique assigned consultant IDs
        $assignedConsultantIds = $assignedConsultantIds->unique()->values();
        
        // If no consultants are assigned, show message
        if ($assignedConsultantIds->isEmpty()) {
            return view('meetings.available-slots', [
                'slots' => collect(),
                'hasAssignedConsultants' => false,
                'serviceRequestType' => null,
            ]);
        }

        // Get slots only for assigned consultants
        $allSlots = $this->timeSlotService->getAvailableSlotsForBooking(null);
        $slots = $allSlots->filter(function($slot) use ($assignedConsultantIds) {
            return $assignedConsultantIds->contains($slot->user_id);
        });

        // Get service requests for dropdown
        $serviceRequests = collect();
        $consultations->each(function($c) use ($serviceRequests) {
            $serviceRequests->push([
                'id' => $c->id,
                'type' => 'consultation',
                'title' => $c->title,
                'assigned_to' => $c->assigned_to,
            ]);
        });
        $research->each(function($r) use ($serviceRequests) {
            $serviceRequests->push([
                'id' => $r->id,
                'type' => 'research',
                'title' => $r->title,
                'assigned_to' => $r->assigned_to,
            ]);
        });
        $ipRegistrations->each(function($ip) use ($serviceRequests) {
            $serviceRequests->push([
                'id' => $ip->id,
                'type' => 'ip',
                'title' => $ip->title,
                'assigned_to' => $ip->assigned_to,
            ]);
        });
        $copyrights->each(function($c) use ($serviceRequests) {
            $serviceRequests->push([
                'id' => $c->id,
                'type' => 'copyright',
                'title' => $c->title,
                'assigned_to' => $c->assigned_to,
            ]);
        });

        $selectedServiceRequest = $request->query('service_request_id');
        $selectedType = $request->query('service_type');

        // Filter slots by selected service request's assigned consultant
        if ($selectedServiceRequest && $selectedType) {
            $selectedRequest = $serviceRequests->first(function($sr) use ($selectedServiceRequest, $selectedType) {
                return $sr['id'] == $selectedServiceRequest && $sr['type'] == $selectedType;
            });
            
            if ($selectedRequest) {
                $slots = $slots->filter(function($slot) use ($selectedRequest) {
                    return $slot->user_id == $selectedRequest['assigned_to'];
                });
            }
        }

        return view('meetings.available-slots', compact('slots', 'serviceRequests', 'selectedServiceRequest', 'selectedType', 'assignedConsultantIds'));
    }

    /**
     * Show booking form for a specific slot (CLIENT)
     * Requires service request selection
     */
    public function create(Request $request, TimeSlot $timeSlot)
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            abort(403);
        }

        // Require service request selection
        $serviceRequestId = $request->query('service_request_id');
        $serviceType = $request->query('service_type');
        
        if (!$serviceRequestId || !$serviceType) {
            return redirect()->route('meetings.available-slots')
                ->withErrors(['error' => 'Please select a service request to book a meeting.']);
        }

        // Verify the consultant is assigned to this service request
        $isAssigned = false;
        $assignedConsultantId = null;
        
        switch ($serviceType) {
            case 'consultation':
                $serviceRequest = \App\Models\ConsultationRequest::where('id', $serviceRequestId)
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                    $assignedConsultantId = $serviceRequest->assigned_to;
                }
                break;
            case 'research':
                $serviceRequest = \App\Models\ResearchRequest::where('id', $serviceRequestId)
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                    $assignedConsultantId = $serviceRequest->assigned_to;
                }
                break;
            case 'ip':
                $serviceRequest = \App\Models\IpRegistration::where('id', $serviceRequestId)
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                    $assignedConsultantId = $serviceRequest->assigned_to;
                }
                break;
            case 'copyright':
                $serviceRequest = \App\Models\CopyrightRegistration::where('id', $serviceRequestId)
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                    $assignedConsultantId = $serviceRequest->assigned_to;
                }
                break;
        }

        if (!$isAssigned) {
            return redirect()->route('meetings.available-slots')
                ->withErrors(['error' => 'This consultant is not assigned to the selected service request.']);
        }

        if (!$timeSlot->isAvailable()) {
            return redirect()->route('meetings.available-slots')
                ->withErrors(['error' => 'This time slot is no longer available.']);
        }

        return view('meetings.create', compact('timeSlot', 'serviceRequestId', 'serviceType'));
    }

    /**
     * Book a meeting (CLIENT) - Uses service
     * Requires service request validation
     */
    public function store(Request $request, TimeSlot $timeSlot)
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|in:30,60,90,120',
            'service_request_id' => 'required|integer',
            'service_type' => 'required|in:consultation,research,ip,copyright',
        ]);

        // Verify the consultant is assigned to this service request
        $isAssigned = false;
        
        switch ($validated['service_type']) {
            case 'consultation':
                $serviceRequest = \App\Models\ConsultationRequest::where('id', $validated['service_request_id'])
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                }
                break;
            case 'research':
                $serviceRequest = \App\Models\ResearchRequest::where('id', $validated['service_request_id'])
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                }
                break;
            case 'ip':
                $serviceRequest = \App\Models\IpRegistration::where('id', $validated['service_request_id'])
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                }
                break;
            case 'copyright':
                $serviceRequest = \App\Models\CopyrightRegistration::where('id', $validated['service_request_id'])
                    ->where('user_id', $user->id)
                    ->whereNotNull('assigned_to')
                    ->first();
                if ($serviceRequest && $serviceRequest->assigned_to == $timeSlot->user_id) {
                    $isAssigned = true;
                }
                break;
        }

        if (!$isAssigned) {
            return back()->withErrors(['error' => 'This consultant is not assigned to the selected service request.']);
        }

        try {
            $meeting = $this->meetingService->bookMeeting($user, $timeSlot, $validated);
            
            return redirect()->route('meetings.my-meetings')
                ->with('success', 'Meeting booked successfully! You will receive confirmation soon.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show all meetings for current user (BOTH SIDES)
     */
    public function myMeetings()
    {
        $user = Auth::user();
        $meetings = $this->meetingService->getUserMeetings($user);

        return view('meetings.my-meetings', compact('meetings'));
    }

    /**
     * Confirm a meeting (INTERNAL TEAM ONLY)
     */
    public function confirm(Request $request, Meeting $meeting)
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $validated = $request->validate([
            'meeting_link' => 'nullable|url',
        ]);

        $this->meetingService->confirmMeeting($meeting, $validated['meeting_link'] ?? null);

        return back()->with('success', 'Meeting confirmed!');
    }

    /**
     * Cancel a meeting (BOTH SIDES)
     */
    public function cancel(Meeting $meeting)
    {
        $user = Auth::user();
        
        // Client can cancel their own meetings, team member can cancel assigned meetings
        if ($meeting->client_id !== $user->id && $meeting->team_member_id !== $user->id && !$user->isManager()) {
            abort(403);
        }

        $this->meetingService->cancelMeeting($meeting);

        return back()->with('success', 'Meeting cancelled.');
    }
}
