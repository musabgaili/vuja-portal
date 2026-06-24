<?php

namespace App\Http\Controllers;

use App\Mail\MeetingBooked;
use App\Mail\MeetingConfirmed;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\TeamMember;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\Meetings\InternalMeetingService;
use App\Services\Notifier;
use App\Services\ServiceRequests\MeetingService;
use App\Services\ServiceRequests\TimeSlotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MeetingController extends Controller
{
    protected $meetingService;

    protected $timeSlotService;

    public function __construct(MeetingService $meetingService, TimeSlotService $timeSlotService)
    {
        $this->middleware(['auth']);

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

        if (! $user->isClient()) {
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
        $slots = $allSlots->filter(function ($slot) use ($assignedConsultantIds) {
            return $assignedConsultantIds->contains($slot->user_id);
        });

        // Get service requests for dropdown
        $serviceRequests = collect();
        $consultations->each(function ($c) use ($serviceRequests) {
            $serviceRequests->push([
                'id' => $c->id,
                'type' => 'consultation',
                'title' => $c->title,
                'assigned_to' => $c->assigned_to,
            ]);
        });
        $research->each(function ($r) use ($serviceRequests) {
            $serviceRequests->push([
                'id' => $r->id,
                'type' => 'research',
                'title' => $r->title,
                'assigned_to' => $r->assigned_to,
            ]);
        });
        $ipRegistrations->each(function ($ip) use ($serviceRequests) {
            $serviceRequests->push([
                'id' => $ip->id,
                'type' => 'ip',
                'title' => $ip->title,
                'assigned_to' => $ip->assigned_to,
            ]);
        });
        $copyrights->each(function ($c) use ($serviceRequests) {
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
            $selectedRequest = $serviceRequests->first(function ($sr) use ($selectedServiceRequest, $selectedType) {
                return $sr['id'] == $selectedServiceRequest && $sr['type'] == $selectedType;
            });

            if ($selectedRequest) {
                $slots = $slots->filter(function ($slot) use ($selectedRequest) {
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

        if (! $user->isClient()) {
            abort(403);
        }

        // Require service request selection
        $serviceRequestId = $request->query('service_request_id');
        $serviceType = $request->query('service_type');

        if (! $serviceRequestId || ! $serviceType) {
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

        if (! $isAssigned) {
            return redirect()->route('meetings.available-slots')
                ->withErrors(['error' => 'This consultant is not assigned to the selected service request.']);
        }

        if (! $timeSlot->isAvailable()) {
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

        if (! $user->isClient()) {
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

        if (! $isAssigned) {
            return back()->withErrors(['error' => 'This consultant is not assigned to the selected service request.']);
        }

        try {
            $meeting = $this->meetingService->bookMeeting($user, $timeSlot, $validated);

            $meeting->load(['client', 'teamMember']);

            if ($meeting->teamMember?->email) {
                Mail::to($meeting->teamMember->email)->send(new MeetingBooked($meeting));
            }
            if ($meeting->client?->email) {
                Mail::to($meeting->client->email)->send(new MeetingBooked($meeting));
            }

            return redirect()->route('meetings.my-meetings')
                ->with('success', 'Meeting booked successfully! You will receive confirmation soon.');
        } catch (\Exception $e) {
            Log::error('Meeting booking failed.', [
                'user_id' => $user->id,
                'time_slot_id' => $timeSlot->id,
                'service_request_id' => $validated['service_request_id'],
                'service_type' => $validated['service_type'],
                'exception' => $e,
            ]);

            return back()->withErrors([
                'error' => 'We could not book this meeting right now. Please try again or choose another available slot.',
            ]);
        }
    }

    /**
     * INTERNAL booking: a team-availability board (staff grouped by specialisation,
     * each with their available slots for the selected week) plus a form to book
     * a meeting at a chosen date/time and invite one or more colleagues.
     */
    public function bookInternal(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        // Week to display on the availability board (defaults to the current week).
        $weekStart = $this->resolveWeek($request->query('week'));
        $weekEnd = $weekStart->copy()->addDays(6);

        $colleagues = User::where('type', 'internal')->orderBy('name')->get(['id', 'name']);
        $specByUser = TeamMember::pluck('specialization', 'user_id');

        // Available, un-booked, future slots in the displayed week, keyed by user.
        $slotsByUser = TimeSlot::with('meeting')
            ->where('status', 'available')
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->whereDoesntHave('meeting')
            ->orderBy('date')->orderBy('start_time')
            ->get()
            ->filter(fn (TimeSlot $s) => ! $s->isPast())
            ->groupBy('user_id');

        // Upcoming available-slot totals (any future date) drive the picker hint.
        $upcomingCounts = TimeSlot::where('status', 'available')
            ->where('date', '>=', today()->toDateString())
            ->whereDoesntHave('meeting')
            ->selectRaw('user_id, count(*) as c')->groupBy('user_id')->pluck('c', 'user_id');

        // Group staff by specialisation for the board + picker (design / electronics / other).
        $groups = $colleagues->groupBy(fn (User $c) => $specByUser[$c->id] ?? 'other')
            ->sortKeys();

        $canForce = $user->isManager() || $user->isProjectManager();
        $preselect = (int) $request->query('member', 0);
        $prefillDate = $request->query('date');

        return view('meetings.book-internal', compact(
            'groups', 'slotsByUser', 'upcomingCounts', 'weekStart', 'weekEnd',
            'canForce', 'preselect', 'prefillDate', 'user'
        ));
    }

    /** Sunday-anchored week start for the availability board. */
    private function resolveWeek(?string $week): Carbon
    {
        try {
            $base = $week ? Carbon::parse($week) : Carbon::today();
        } catch (\Throwable $e) {
            $base = Carbon::today();
        }

        return $base->startOfWeek(Carbon::SUNDAY);
    }

    /**
     * INTERNAL booking: create a multi-attendee meeting at a chosen date/time.
     * The service decides per colleague whether to book an existing slot, send a
     * recommendation to accept, or force-create a slot (organiser / manager-PM).
     */
    public function storeInternal(Request $request, InternalMeetingService $service)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|in:30,60,90,120',
            'attendee_ids' => 'required|array|min:1',
            'attendee_ids.*' => 'integer|distinct|exists:users,id',
            'include_self' => 'nullable|boolean',
        ]);

        $attendeeIds = collect($validated['attendee_ids'])->map(fn ($i) => (int) $i);
        if ($request->boolean('include_self')) {
            $attendeeIds->push($user->id);
        }
        $validated['attendee_ids'] = $attendeeIds->unique()->values()->all();

        try {
            $outcome = $service->book($user, $validated);
        } catch (\Throwable $e) {
            Log::error('Internal meeting booking failed.', ['user_id' => $user->id, 'exception' => $e->getMessage()]);

            return back()->withInput()->withErrors(['error' => $e->getMessage() ?: __('portal.meetings.book_failed')]);
        }

        $this->notifyAttendees($outcome['meeting'], $outcome['results'], $user);

        // Refresh the bell/nav badges for anyone newly invited.
        foreach ($outcome['results'] as $uid => $r) {
            if ($r['outcome'] === 'invited') {
                Cache::forget('notif_feed:'.$uid);
                Cache::forget('mtg_inv:'.$uid);
            }
        }

        // Summarise what happened per attendee for the flash message.
        $booked = collect($outcome['results'])->whereIn('outcome', ['booked', 'force_booked'])->count();
        $invited = collect($outcome['results'])->where('outcome', 'invited')->count();

        return redirect()->route('meetings.internal.my-meetings')
            ->with('success', __('portal.meetings.booked_summary', ['booked' => $booked, 'invited' => $invited]));
    }

    /**
     * Email each colleague (best-effort): a "booked" notice to confirmed
     * attendees and an "invitation to accept" to those who must respond. The
     * organiser is not emailed about their own action.
     */
    private function notifyAttendees(Meeting $meeting, array $results, User $organizer): void
    {
        $notifier = app(Notifier::class);
        $url = route('meetings.invitations');
        $when = $meeting->scheduled_at->translatedFormat('l, M j, Y g:i A');

        foreach ($results as $uid => $r) {
            if ((int) $uid === (int) $organizer->id) {
                continue;
            }
            $target = User::find($uid);
            if (! $target) {
                continue;
            }

            if ($r['outcome'] === 'invited') {
                $notifier->email($target, 'meeting_invitation',
                    __('portal.meetings.invite_email_subject', ['name' => $organizer->name]),
                    __('portal.meetings.invite_email_heading'),
                    __('portal.meetings.invite_email_body', ['name' => $organizer->name, 'title' => $meeting->title, 'when' => $when]),
                    $url, __('portal.meetings.invitations_title'));
            } else {
                $notifier->email($target, 'meeting_invitation',
                    __('portal.meetings.booked_email_subject', ['name' => $organizer->name]),
                    __('portal.meetings.booked_email_heading'),
                    __('portal.meetings.booked_email_body', ['name' => $organizer->name, 'title' => $meeting->title, 'when' => $when]),
                    route('meetings.internal.my-meetings'), __('portal.meetings.my_meetings'));
            }
        }
    }

    /**
     * The current user's meeting invitations awaiting their response, plus a
     * short history of the ones they've answered.
     */
    public function invitations()
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $pending = MeetingAttendee::with(['meeting.client'])
            ->where('user_id', $user->id)->where('status', 'invited')
            ->whereHas('meeting', fn ($q) => $q->where('status', '!=', 'cancelled'))
            ->get()
            ->filter(fn (MeetingAttendee $a) => $a->meeting && $a->meeting->scheduled_at?->isFuture())
            ->sortBy(fn (MeetingAttendee $a) => $a->meeting->scheduled_at)
            ->values();

        $answered = MeetingAttendee::with(['meeting.client'])
            ->where('user_id', $user->id)->whereIn('status', ['accepted', 'declined'])
            ->latest('responded_at')->limit(10)->get();

        return view('meetings.invitations', compact('pending', 'answered'));
    }

    public function acceptInvitation(MeetingAttendee $attendee, InternalMeetingService $service)
    {
        $this->authorizeInvitation($attendee);

        $service->accept($attendee);
        Cache::forget('notif_feed:'.$attendee->user_id);
        Cache::forget('mtg_inv:'.$attendee->user_id);

        return back()->with('success', __('portal.meetings.invite_accepted'));
    }

    public function declineInvitation(MeetingAttendee $attendee, InternalMeetingService $service)
    {
        $this->authorizeInvitation($attendee);

        $service->decline($attendee);
        Cache::forget('notif_feed:'.$attendee->user_id);
        Cache::forget('mtg_inv:'.$attendee->user_id);

        return back()->with('success', __('portal.meetings.invite_declined'));
    }

    /** Only the invited colleague may answer their own pending invitation. */
    private function authorizeInvitation(MeetingAttendee $attendee): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal() && (int) $attendee->user_id === (int) $user->id, 403);
        abort_unless($attendee->status === 'invited', 422, __('portal.meetings.invite_already_answered'));
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

        if (! $user->isInternal()) {
            abort(403);
        }
        $this->authorize('update', $meeting);

        $validated = $request->validate([
            'meeting_link' => 'nullable|url',
        ]);

        $this->meetingService->confirmMeeting($meeting, $validated['meeting_link'] ?? null);

        $meeting->refresh()->loadMissing('client');
        if ($meeting->client?->email) {
            Mail::to($meeting->client->email)->send(new MeetingConfirmed($meeting));
        }

        return back()->with('success', 'Meeting confirmed!');
    }

    /**
     * Mark a meeting as attended/completed. This is the discrete "it happened"
     * event behind the "meetings attended" target + its gated points.
     */
    public function complete(Meeting $meeting)
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal(), 403);
        // The primary host, the organiser, or a manager/PM may close out a meeting.
        abort_unless(
            (int) $meeting->team_member_id === (int) $user->id
            || (int) $meeting->client_id === (int) $user->id
            || $user->isManager() || $user->isProjectManager(),
            403
        );

        if ($meeting->status !== 'completed') {
            $meeting->update(['status' => 'completed', 'completed_at' => now()]);

            // Credit every internal person who attended (primary host + accepted
            // attendees) with target-gated "meeting attended" points.
            $gate = app(\App\Services\Targets\TargetPointsGate::class);
            foreach ($meeting->attendeeUserIds() as $uid) {
                if ($staff = \App\Models\User::find($uid)) {
                    $gate->awardIfEarned($staff, 'meeting_attended', $meeting, 'Meeting attended');
                }
            }
        }

        return back()->with('success', __('targets.meeting_marked'));
    }

    /**
     * Cancel a meeting (BOTH SIDES)
     */
    public function cancel(Meeting $meeting)
    {
        $user = Auth::user();

        $this->authorize('update', $meeting);

        $this->meetingService->cancelMeeting($meeting);

        return back()->with('success', 'Meeting cancelled.');
    }
}
