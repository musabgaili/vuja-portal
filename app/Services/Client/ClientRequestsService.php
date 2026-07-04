<?php

namespace App\Services\Client;

use App\Models\ConsultationRequest;
use App\Models\CopyrightRegistration;
use App\Models\IdeaRequest;
use App\Models\IpRegistration;
use App\Models\ResearchRequest;
use App\Models\ThreeDRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * The client "My Requests" unified feed — merges the client's requests across
 * the service-line models (idea, consultation, research, IP, copyright, 3D) into
 * one chronological, filterable, paginated feed with DB-side summary counts.
 *
 * Extracted verbatim from ClientRequestsController@index so the web page and the
 * mobile API share one source of truth (numbers + ordering can't drift). The web
 * behaviour is unchanged — guarded by tests/Feature/ClientRequestsWebTest.php.
 * (Prototypes intentionally excluded here — they have their own list, mirroring
 * the original web behaviour.)
 */
class ClientRequestsService
{
    /**
     * Build the paginated unified feed + summary for a client.
     *
     * @return array{requests: LengthAwarePaginator, summary: array<string,int>}
     */
    public function build(int $userId, ?string $statusFilter, ?string $typeFilter, int $perPage = 15): array
    {
        // Merge heterogeneous models in PHP, so bound how many rows each hydrates
        // (latest page*perPage, hard-capped) instead of a client's whole history.
        // Summary totals are computed DB-side so they stay accurate.
        $page = Paginator::resolveCurrentPage();
        $window = min($page * $perPage, 300);

        $allRequests = collect();

        foreach (IdeaRequest::where('user_id', $userId)->with('assignedTo')->latest('updated_at')
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))->limit($window)->get() as $idea) {
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
                'has_quote' => (bool) $idea->final_quote,
                'quote_amount' => $idea->final_quote,
                'assigned_to' => $idea->assignedTo?->name,
            ]);
        }

        foreach (ConsultationRequest::where('user_id', $userId)->with('assignedTo')->latest('updated_at')
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))->limit($window)->get() as $consultation) {
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

        foreach (ResearchRequest::where('user_id', $userId)->with('assignedTo')->latest('updated_at')
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))->limit($window)->get() as $research) {
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

        foreach (IpRegistration::where('user_id', $userId)->with('assignedTo')->latest('updated_at')
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))->limit($window)->get() as $ip) {
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

        foreach (CopyrightRegistration::where('user_id', $userId)->with('assignedTo')->latest('updated_at')
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))->limit($window)->get() as $copyright) {
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

        foreach (ThreeDRequest::where('user_id', $userId)->with('assignedTo')->latest('updated_at')
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))->limit($window)->get() as $threed) {
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

        if ($typeFilter) {
            $allRequests = $allRequests->where('type', $typeFilter);
        }

        $sorted = $allRequests->sortByDesc('updated_at')->values();
        $summary = $this->summaryCounts($userId, $statusFilter, $typeFilter);

        $requests = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $summary['total'],
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->query()],
        );

        return ['requests' => $requests, 'summary' => $summary];
    }

    /**
     * DB-side summary counts (accurate regardless of the paged window). The status
     * filter narrows every model; the type filter restricts which models contribute.
     *
     * @return array<string,int>
     */
    public function summaryCounts(int $userId, ?string $statusFilter, ?string $typeFilter): array
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

    /** Feed items for the API — the web view_url is stripped (the app navigates natively). */
    public function apiItems(Collection $items): array
    {
        return $items->map(function (array $i) {
            unset($i['view_url']);

            return $i;
        })->all();
    }
}
