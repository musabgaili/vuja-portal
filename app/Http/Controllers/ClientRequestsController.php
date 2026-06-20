<?php

namespace App\Http\Controllers;

use App\Models\ConsultationRequest;
use App\Models\CopyrightRegistration;
use App\Models\IdeaRequest;
use App\Models\IpRegistration;
use App\Models\ResearchRequest;
use App\Models\ThreeDRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class ClientRequestsController extends Controller
{
    /**
     * Display all client requests across all services.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $statusFilter = $request->get('status');
        $typeFilter = $request->get('type');

        // Pagination: we merge six heterogeneous models in PHP, so we bound how
        // many rows each one hydrates instead of fetching a client's entire
        // history. Fetching the latest (page * perPage) from each model is enough
        // to render the requested page correctly once merged + sorted; the hard
        // cap keeps memory bounded for very deep pages. Summary counts are computed
        // DB-side (below) so they stay accurate regardless of this window.
        $perPage = 15;
        $page = Paginator::resolveCurrentPage();
        $window = min($page * $perPage, 300);

        // Collect all requests from all services
        $allRequests = collect();

        // Get Ideas
        $ideasQuery = IdeaRequest::where('user_id', $user->id)->with('assignedTo')->latest('updated_at')->limit($window);
        if ($statusFilter) {
            $ideasQuery->where('status', $statusFilter);
        }
        foreach ($ideasQuery->get() as $idea) {
            $allRequests->push([
                'id' => $idea->id,
                'type' => 'idea',
                'type_label' => __('portal.client.requests.type_idea'),
                'type_icon' => 'lightbulb',
                'type_color' => '#0F969C',
                'title' => $idea->tr('title'),
                'description' => $idea->tr('description'),
                'status' => $idea->status,
                'status_label' => $idea->getStatusLabel(),
                'status_color' => $idea->getStatusBadgeColor(),
                'created_at' => $idea->created_at,
                'updated_at' => $idea->updated_at,
                'view_url' => route('ideas.show', $idea),
                'has_quote' => $idea->final_quote ? true : false,
                'quote_amount' => $idea->final_quote,
                'assigned_to' => $idea->assignedTo?->name,
            ]);
        }

        // Get Consultations
        $consultationsQuery = ConsultationRequest::where('user_id', $user->id)->with('assignedTo')->latest('updated_at')->limit($window);
        if ($statusFilter) {
            $consultationsQuery->where('status', $statusFilter);
        }
        foreach ($consultationsQuery->get() as $consultation) {
            $allRequests->push([
                'id' => $consultation->id,
                'type' => 'consultation',
                'type_label' => __('portal.client.requests.type_consultation'),
                'type_icon' => 'comments',
                'type_color' => '#0C7075',
                'title' => $consultation->tr('title'),
                'description' => $consultation->tr('description'),
                'status' => $consultation->status,
                'status_label' => $consultation->getStatusLabel(),
                'status_color' => $consultation->getStatusBadgeColor(),
                'created_at' => $consultation->created_at,
                'updated_at' => $consultation->updated_at,
                'view_url' => route('consultations.show', $consultation),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $consultation->assignedTo?->name,
                'meeting_date' => $consultation->meeting_scheduled_at,
            ]);
        }

        // Get Research
        $researchQuery = ResearchRequest::where('user_id', $user->id)->with('assignedTo')->latest('updated_at')->limit($window);
        if ($statusFilter) {
            $researchQuery->where('status', $statusFilter);
        }
        foreach ($researchQuery->get() as $research) {
            $allRequests->push([
                'id' => $research->id,
                'type' => 'research',
                'type_label' => __('portal.client.requests.type_research'),
                'type_icon' => 'search',
                'type_color' => '#294D61',
                'title' => $research->tr('title'),
                'description' => $research->tr('research_topic'),
                'status' => $research->status,
                'status_label' => $research->getStatusLabel(),
                'status_color' => $research->getStatusBadgeColor(),
                'created_at' => $research->created_at,
                'updated_at' => $research->updated_at,
                'view_url' => route('research.show', $research),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $research->assignedTo?->name,
                'meeting_date' => $research->meeting_scheduled_at,
            ]);
        }

        // Get IP Registrations
        $ipQuery = IpRegistration::where('user_id', $user->id)->with('assignedTo')->latest('updated_at')->limit($window);
        if ($statusFilter) {
            $ipQuery->where('status', $statusFilter);
        }
        foreach ($ipQuery->get() as $ip) {
            $allRequests->push([
                'id' => $ip->id,
                'type' => 'ip',
                'type_label' => __('portal.client.requests.type_ip'),
                'type_icon' => 'file-contract',
                'type_color' => '#2C3F43',
                'title' => $ip->tr('title'),
                'description' => $ip->tr('ip_description'),
                'status' => $ip->status,
                'status_label' => $ip->getStatusLabel(),
                'status_color' => $ip->getStatusBadgeColor(),
                'created_at' => $ip->created_at,
                'updated_at' => $ip->updated_at,
                'view_url' => route('ip.show', $ip),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $ip->assignedTo?->name,
                'meeting_date' => $ip->meeting_requested_at,
                'registration_number' => $ip->registration_number,
            ]);
        }

        // Get Copyrights
        $copyrightQuery = CopyrightRegistration::where('user_id', $user->id)->with('assignedTo')->latest('updated_at')->limit($window);
        if ($statusFilter) {
            $copyrightQuery->where('status', $statusFilter);
        }
        foreach ($copyrightQuery->get() as $copyright) {
            $allRequests->push([
                'id' => $copyright->id,
                'type' => 'copyright',
                'type_label' => __('portal.client.requests.type_copyright'),
                'type_icon' => 'copyright',
                'type_color' => '#072E33',
                'title' => $copyright->tr('title'),
                'description' => $copyright->tr('work_description'),
                'status' => $copyright->status,
                'status_label' => $copyright->getStatusLabel(),
                'status_color' => $copyright->getStatusBadgeColor(),
                'created_at' => $copyright->created_at,
                'updated_at' => $copyright->updated_at,
                'view_url' => route('copyright.show', $copyright),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $copyright->assignedTo?->name,
                'meeting_date' => $copyright->meeting_requested_at,
                'registration_number' => $copyright->copyright_number,
            ]);
        }

        // Get 3D Lab requests (printing + design)
        $threeDQuery = ThreeDRequest::where('user_id', $user->id)->with('assignedTo')->latest('updated_at')->limit($window);
        if ($statusFilter) {
            $threeDQuery->where('status', $statusFilter);
        }
        foreach ($threeDQuery->get() as $threed) {
            $allRequests->push([
                'id' => $threed->id,
                'type' => 'threed',
                'type_label' => $threed->typeLabel(),
                'type_icon' => $threed->isPrinting() ? 'print' : 'pen-ruler',
                'type_color' => '#0C7075',
                'title' => $threed->tr('title'),
                'description' => $threed->tr('description'),
                'status' => $threed->status,
                'status_label' => $threed->getStatusLabel(),
                'status_color' => $threed->getStatusBadgeColor(),
                'created_at' => $threed->created_at,
                'updated_at' => $threed->updated_at,
                'view_url' => route('threed.show', $threed),
                'has_quote' => false,
                'quote_amount' => null,
                'assigned_to' => $threed->assignedTo?->name,
            ]);
        }

        // Filter by type if specified
        if ($typeFilter) {
            $allRequests = $allRequests->where('type', $typeFilter);
        }

        // Sort the bounded feed, then page it in-memory. The total comes from the
        // DB-side summary so the pager spans the client's full history even though
        // we only hydrated a window of rows above.
        $sorted = $allRequests->sortByDesc('updated_at')->values();

        $summary = $this->summaryCounts($user->id, $statusFilter, $typeFilter);

        $allRequests = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $summary['total'],
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => $request->query()],
        );

        return view('client.requests', compact('allRequests', 'summary', 'statusFilter', 'typeFilter'));
    }

    /**
     * Summary counts computed DB-side so they stay accurate regardless of the
     * paginated window. Mirrors the index() filter semantics: the status filter
     * narrows every model, and the type filter restricts which models contribute.
     * One grouped query per model — no row hydration.
     */
    private function summaryCounts(int $userId, ?string $statusFilter, ?string $typeFilter): array
    {
        $models = [
            'idea' => IdeaRequest::class,
            'consultation' => ConsultationRequest::class,
            'research' => ResearchRequest::class,
            'ip' => IpRegistration::class,
            'copyright' => CopyrightRegistration::class,
            'threed' => ThreeDRequest::class,
        ];
        $typeKey = [
            'idea' => 'ideas', 'consultation' => 'consultations', 'research' => 'research',
            'ip' => 'ip', 'copyright' => 'copyright', 'threed' => 'threed',
        ];
        $pending = ['submitted', 'draft', 'nda_pending'];
        $inProgress = ['negotiation', 'assigned', 'in_progress', 'meeting_scheduled'];

        $summary = array_fill_keys(
            ['total', 'ideas', 'consultations', 'research', 'ip', 'copyright', 'threed', 'pending', 'in_progress', 'completed'],
            0,
        );

        foreach ($models as $type => $class) {
            if ($typeFilter && $typeFilter !== $type) {
                continue;
            }
            $base = $class::where('user_id', $userId);
            if ($statusFilter) {
                $base->where('status', $statusFilter);
            }
            $byStatus = $base->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');

            $count = (int) $byStatus->sum();
            $summary[$typeKey[$type]] = $count;
            $summary['total'] += $count;

            foreach ($byStatus as $status => $c) {
                if (in_array($status, $pending, true)) {
                    $summary['pending'] += (int) $c;
                } elseif (in_array($status, $inProgress, true)) {
                    $summary['in_progress'] += (int) $c;
                } elseif ($status === 'completed') {
                    $summary['completed'] += (int) $c;
                }
            }
        }

        return $summary;
    }
}
