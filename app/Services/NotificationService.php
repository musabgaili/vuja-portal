<?php

namespace App\Services;

use App\Models\EngagementLog;
use App\Models\ImprovementIdea;
use App\Models\ProjectTask;
use App\Models\Quote;
use App\Models\StaffTask;
use App\Models\User;
use App\Models\WeeklyPlan;
use Illuminate\Support\Facades\Cache;

/**
 * Lightweight notification feed computed from existing tables (no notifications
 * table). Aggregates the recent items relevant to a user — assigned tasks,
 * timesheet reviews, IP awards, and (for managers) plans awaiting review.
 * Cached briefly per user since the bell renders on every internal page.
 */
class NotificationService
{
    /** Recent items for the bell dropdown, newest first. */
    public function feed(User $user, int $limit = 12): array
    {
        return Cache::remember('notif_feed:'.$user->id, 30, fn () => $this->build($user, $limit));
    }

    /** How many feed items are newer than the user's last "seen" time. */
    public function unreadCount(User $user): int
    {
        $seen = (int) Cache::get('notif_seen:'.$user->id, 0);

        return collect($this->feed($user))->filter(fn ($i) => $i['at'] > $seen)->count();
    }

    /** Mark the feed as seen up to now. */
    public function markSeen(User $user): void
    {
        Cache::put('notif_seen:'.$user->id, now()->timestamp, now()->addDays(30));
    }

    private function build(User $user, int $limit): array
    {
        $items = [];
        $push = function (?string $when, string $icon, string $text, string $url) use (&$items) {
            $ts = $when ? strtotime($when) : 0;
            $items[] = ['icon' => $icon, 'text' => $text, 'url' => $url, 'at' => $ts ?: 0, 'ago' => $ts ? \Carbon\Carbon::createFromTimestamp($ts)->diffForHumans() : ''];
        };

        foreach (StaffTask::where('assigned_to', $user->id)->latest('updated_at')->limit(5)->get() as $t) {
            $push($t->updated_at, 'fa-list-check', __('portal.notif.staff_task', ['title' => $t->title]), route('staff-tasks.index'));
        }

        foreach (ProjectTask::where('assigned_to', $user->id)->with('project')->latest('updated_at')->limit(5)->get() as $t) {
            $url = $t->project ? route('projects.manager.show', $t->project) : route('internal.dashboard');
            $push($t->updated_at, 'fa-diagram-project', __('portal.notif.project_task', ['title' => $t->title]), $url);
        }

        foreach (WeeklyPlan::where('user_id', $user->id)->whereIn('status', ['approved', 'rejected'])
            ->whereNotNull('reviewed_at')->latest('reviewed_at')->limit(3)->get() as $p) {
            $push($p->reviewed_at, 'fa-calendar-check', __('portal.notif.plan_'.$p->status), route('weekly-planner.index'));
        }

        foreach (EngagementLog::where('user_id', $user->id)->latest()->limit(3)->get() as $l) {
            $sign = $l->points > 0 ? '+' : '';
            $push($l->created_at, 'fa-bolt', __('portal.notif.ip', ['points' => $sign.$l->points]), route('engagement.index'));
        }

        // Outcomes on the user's own portal-improvement ideas (approved / rejected).
        foreach (ImprovementIdea::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'rejected', 'implemented'])
            ->whereNotNull('reviewed_at')->latest('reviewed_at')->limit(5)->get() as $idea) {
            $push($idea->reviewed_at, $idea->status === 'rejected' ? 'fa-circle-xmark' : 'fa-rocket',
                __('portal.notif.improvement_'.$idea->status, ['title' => $idea->title]),
                route('improvement-ideas.show', $idea));
        }

        if ($user->isManager()) {
            foreach (WeeklyPlan::where('status', 'pending')->with('user')->whereNotNull('submitted_at')
                ->latest('submitted_at')->limit(5)->get() as $p) {
                $push($p->submitted_at, 'fa-clipboard-check', __('portal.notif.plan_review', ['name' => $p->user?->name ?? '—']), route('weekly-planner.review'));
            }

            // New portal-improvement ideas awaiting the manager's review.
            foreach (ImprovementIdea::where('status', 'submitted')->with('user')
                ->latest()->limit(5)->get() as $idea) {
                $push($idea->created_at, 'fa-rocket', __('portal.notif.improvement_new', ['name' => $idea->user?->name ?? '—', 'title' => $idea->title]), route('improvement-ideas.manager.index'));
            }
        }

        // Outcomes on quotes the user submitted (approved / rejected / sent back).
        foreach (Quote::where('created_by', $user->id)
            ->whereIn('status', ['approved', 'rejected', 'changes_requested'])
            ->latest('updated_at')->limit(5)->get() as $q) {
            $push($q->updated_at, 'fa-file-invoice', __('portal.notif.quote_'.$q->status, ['title' => $q->title]), route('quotes.show', $q));
        }

        // Quotes awaiting this approver's decision.
        if ($user->isManager() || $user->isProjectManager()) {
            foreach (Quote::where('status', 'pending_approval')->with('creator')
                ->latest('updated_at')->limit(5)->get() as $q) {
                $push($q->updated_at, 'fa-gavel', __('portal.notif.quote_pending', ['name' => $q->creator->name ?? '—', 'title' => $q->title]), route('approvals.index'));
            }
        }

        usort($items, fn ($a, $b) => $b['at'] <=> $a['at']);

        return array_slice($items, 0, $limit);
    }
}
