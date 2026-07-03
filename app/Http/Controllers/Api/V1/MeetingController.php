<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MeetingResource;
use App\Models\Meeting;
use App\Services\ServiceRequests\MeetingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Read access to the user's meetings (reuses the same MeetingService as the web). */
class MeetingController extends Controller
{
    public function __construct(private MeetingService $meetings) {}

    /** Paginated list of the user's meetings (hosted, organised, invited, or as client). */
    public function index(Request $request): AnonymousResourceCollection
    {
        return MeetingResource::collection($this->meetings->getUserMeetings($request->user()));
    }

    /** A single meeting the user participates in (or manages). */
    public function show(Request $request, Meeting $meeting): MeetingResource
    {
        $user = $request->user();

        abort_unless(
            (int) $meeting->client_id === (int) $user->id
            || (int) $meeting->team_member_id === (int) $user->id
            || $user->isManager()
            || $user->isProjectManager()
            || $meeting->attendees()->where('user_id', $user->id)->exists(),
            403,
        );

        $meeting->load(['client', 'teamMember', 'attendees.user', 'timeSlot']);

        return new MeetingResource($meeting);
    }
}
