<?php

namespace App\Services\Dashboard;

use App\Models\ConsultationRequest;
use App\Models\CopyrightRegistration;
use App\Models\IdeaRequest;
use App\Models\IpRegistration;
use App\Models\ResearchRequest;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Client home-screen data, shared by the web dashboard and the mobile API so
 * the numbers stay identical. stats() is the single source of truth for the
 * headline counters; the web keeps its own icon/colour presentation for the
 * recent/active lists, while the API consumes the neutral lists below.
 */
class ClientDashboardService
{
    /**
     * Headline counters for the client, computed with queries (not collections).
     * Mirrors the web DashboardController exactly.
     *
     * @return array<string, int>
     */
    public function stats(User $user): array
    {
        return [
            // Projects stats
            'active_projects' => IdeaRequest::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'in_progress'])
                ->count() +
                                ResearchRequest::where('user_id', $user->id)
                                    ->where('status', 'in_progress')
                                    ->count(),

            'pending_projects' => IdeaRequest::where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'negotiation', 'quoted'])
                ->count() +
                                 ConsultationRequest::where('user_id', $user->id)
                                     ->whereIn('status', ['submitted', 'filtered'])
                                     ->count() +
                                 ResearchRequest::where('user_id', $user->id)
                                     ->whereIn('status', ['submitted', 'nda_pending'])
                                     ->count(),

            'completed_projects' => IdeaRequest::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   ConsultationRequest::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   ResearchRequest::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   IpRegistration::where('user_id', $user->id)->where('status', 'completed')->count() +
                                   CopyrightRegistration::where('user_id', $user->id)->where('status', 'completed')->count(),

            // Service requests stats
            'requests_in_review' => IdeaRequest::where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'negotiation'])
                ->count() +
                                   ConsultationRequest::where('user_id', $user->id)
                                       ->whereIn('status', ['submitted', 'filtered', 'assigned'])
                                       ->count() +
                                   ResearchRequest::where('user_id', $user->id)
                                       ->whereIn('status', ['submitted', 'nda_pending', 'nda_signed'])
                                       ->count(),

            'requests_approved' => IdeaRequest::where('user_id', $user->id)
                ->whereIn('status', ['approved', 'in_progress'])
                ->count() +
                                  ConsultationRequest::where('user_id', $user->id)
                                      ->where('status', 'meeting_sent')
                                      ->count() +
                                  ResearchRequest::where('user_id', $user->id)
                                      ->where('status', 'in_progress')
                                      ->count(),

            // Meetings stats
            'meetings_this_week' => ConsultationRequest::where('user_id', $user->id)
                ->whereBetween('meeting_scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count() +
                                   ResearchRequest::where('user_id', $user->id)
                                       ->whereBetween('meeting_scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                                       ->count() +
                                   IpRegistration::where('user_id', $user->id)
                                       ->whereBetween('meeting_requested_at', [now()->startOfWeek(), now()->endOfWeek()])
                                       ->count() +
                                   CopyrightRegistration::where('user_id', $user->id)
                                       ->whereBetween('meeting_requested_at', [now()->startOfWeek(), now()->endOfWeek()])
                                       ->count(),

            'meetings_today' => ConsultationRequest::where('user_id', $user->id)
                ->whereDate('meeting_scheduled_at', today())
                ->count() +
                               ResearchRequest::where('user_id', $user->id)
                                   ->whereDate('meeting_scheduled_at', today())
                                   ->count() +
                               IpRegistration::where('user_id', $user->id)
                                   ->whereDate('meeting_requested_at', today())
                                   ->count() +
                               CopyrightRegistration::where('user_id', $user->id)
                                   ->whereDate('meeting_requested_at', today())
                                   ->count(),

            // AI tokens
            'total_tokens' => (int) IdeaRequest::where('user_id', $user->id)->sum('tokens_used'),
            'ai_assessments' => IdeaRequest::where('user_id', $user->id)->whereNotNull('ai_assessment_data')->count(),
        ];
    }

    /**
     * Recent request activity as neutral data (no web icons/colours), newest first.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function recentActivity(User $user, int $limit = 10): Collection
    {
        $items = collect();

        foreach (IdeaRequest::where('user_id', $user->id)->latest('updated_at')->take(3)->get() as $idea) {
            $items->push($this->activityItem('idea', $idea));
        }
        foreach (ConsultationRequest::where('user_id', $user->id)->latest('updated_at')->take(3)->get() as $c) {
            $items->push($this->activityItem('consultation', $c));
        }
        foreach (ResearchRequest::where('user_id', $user->id)->latest('updated_at')->take(2)->get() as $r) {
            $items->push($this->activityItem('research', $r));
        }

        return $items->sortByDesc('updated_at')->take($limit)->values();
    }

    /**
     * The client's active projects as neutral data.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function activeProjects(User $user): Collection
    {
        $items = collect();

        foreach (IdeaRequest::where('user_id', $user->id)->whereIn('status', ['approved', 'in_progress'])->take(5)->get() as $idea) {
            $items->push($this->activityItem('idea', $idea));
        }
        foreach (ResearchRequest::where('user_id', $user->id)->where('status', 'in_progress')->take(3)->get() as $r) {
            $items->push($this->activityItem('research', $r));
        }

        return $items->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function activityItem(string $type, $model): array
    {
        return [
            'type' => $type,
            'id' => $model->id,
            'title' => $model->title,
            'status' => $model->status,
            'status_label' => method_exists($model, 'getStatusLabel') ? $model->getStatusLabel() : $model->status,
            'updated_at' => optional($model->updated_at)->toIso8601String(),
        ];
    }
}
