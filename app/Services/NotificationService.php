<?php

namespace App\Services;

use App\Models\EngagementLog;
use App\Models\ImprovementIdea;
use App\Models\ProjectTask;
use App\Models\Quote;
use App\Models\SpendRequest;
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
    /** The full cached feed (one key per user so forget() keeps working). */
    private function cached(User $user): array
    {
        // ~17 queries to rebuild; the bell is a passive feed, so a longer TTL is fine.
        return Cache::remember('notif_feed:'.$user->id, 120, fn () => $this->build($user, 50));
    }

    /** Recent items, newest first (sliced to the caller's limit). */
    public function feed(User $user, int $limit = 12): array
    {
        return array_slice($this->cached($user), 0, $limit);
    }

    /** How many feed items are newer than the user's last "seen" time. */
    public function unreadCount(User $user): int
    {
        $seen = (int) Cache::get('notif_seen:'.$user->id, 0);

        return collect($this->cached($user))->filter(fn ($i) => $i['at'] > $seen)->count();
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

        // Unread chat @mentions → the bell + Mentions inbox.
        $mentionedChannels = [];
        foreach (\App\Models\ChatMessageMention::where('user_id', $user->id)->whereNull('read_at')
            ->with(['message.author', 'message.channel'])->latest('id')->limit(5)->get() as $mn) {
            if (! $mn->message || $mn->message->trashed()) {
                continue;
            }
            $mentionedChannels[$mn->message->chat_channel_id] = true;
            $push($mn->created_at, 'fa-at',
                __('portal.notif.chat_mention', ['name' => $mn->message->author?->name ?? '—']),
                route('chat.show', $mn->message->chat_channel_id));
        }

        // Unread chat conversations (DMs + channels) → the bell. So a plain direct
        // message (no @mention) still notifies the recipient. Skip channels already
        // surfaced as a mention above to avoid a duplicate bell entry.
        foreach (app(ChatService::class)->unreadConversations($user) as $conv) {
            if (isset($mentionedChannels[$conv['channel_id']])) {
                continue;
            }
            $icon = $conv['is_dm'] ? 'fa-comment-dots' : 'fa-hashtag';
            $push($conv['at'], $icon,
                __('portal.notif.chat_message', ['name' => $conv['title'], 'count' => $conv['count']]),
                route('chat.show', $conv['channel_id']));
        }

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

        // Spend/reimbursement: outcomes on the user's own requests.
        foreach (SpendRequest::where('requester_id', $user->id)
            ->whereIn('status', ['approved', 'rejected', 'completed'])
            ->latest('updated_at')->limit(5)->get() as $sr) {
            $push($sr->updated_at, 'fa-receipt', __('portal.notif.spend_'.$sr->status, ['title' => $sr->title]), route('spend.show', $sr));
        }

        // Spend requests routed to this user to review.
        foreach (SpendRequest::with(['requester', 'project'])->where('status', 'pending')
            ->where('requester_id', '!=', $user->id)->latest()->limit(10)->get() as $sr) {
            if ($sr->isApprovableBy($user)) {
                $push($sr->created_at, 'fa-inbox', __('portal.notif.spend_review', ['name' => $sr->requester->name ?? '—', 'title' => $sr->title]), route('spend.show', $sr));
            }
        }

        // Meeting invitations awaiting this user's accept/decline.
        if ($user->isInternal()) {
            foreach (\App\Models\MeetingAttendee::with('meeting.client')->where('user_id', $user->id)
                ->where('status', 'invited')->latest()->limit(5)->get() as $inv) {
                if (! $inv->meeting || $inv->meeting->status === 'cancelled' || ! $inv->meeting->scheduled_at?->isFuture()) {
                    continue;
                }
                $push($inv->created_at, 'fa-calendar-plus',
                    __('portal.notif.meeting_invite', ['name' => $inv->meeting->client?->name ?? '—', 'title' => $inv->meeting->title]),
                    route('meetings.invitations'));
            }
        }

        if ($user->isInternal()) {
            // Projects you were added to, recently created.
            foreach (\App\Models\Project::where('created_at', '>=', now()->subDays(14))
                ->where(function ($q) use ($user) {
                    $q->where('project_manager_id', $user->id)
                        ->orWhere('account_manager_id', $user->id)
                        ->orWhereHas('projectPeople', fn ($p) => $p->where('user_id', $user->id));
                })->latest()->limit(5)->get() as $proj) {
                $push($proj->created_at, 'fa-diagram-project',
                    __('portal.notif.project_created', ['title' => $proj->title]),
                    route('projects.manager.show', $proj));
            }

            // New milestones on your projects.
            foreach (\App\Models\ProjectMilestone::with('project')
                ->where('created_at', '>=', now()->subDays(14))
                ->whereHas('project', function ($q) use ($user) {
                    $q->where('project_manager_id', $user->id)
                        ->orWhere('account_manager_id', $user->id)
                        ->orWhereHas('projectPeople', fn ($p) => $p->where('user_id', $user->id));
                })->latest()->limit(5)->get() as $ms) {
                $push($ms->created_at, 'fa-flag-checkered',
                    __('portal.notif.milestone_set', ['title' => $ms->title, 'project' => $ms->project?->title ?? '—']),
                    route('projects.manager.show', $ms->project_id));
            }

            // Direct tasks you assigned that the assignee finished.
            foreach (StaffTask::where('assigned_by', $user->id)->where('status', 'done')
                ->whereNotNull('completed_at')->where('completed_at', '>=', now()->subDays(7))
                ->latest('completed_at')->limit(5)->get() as $st) {
                $push($st->completed_at, 'fa-circle-check',
                    __('portal.notif.task_done', ['title' => $st->title]), route('staff-tasks.index'));
            }

            // Project tasks you created that were completed.
            foreach (ProjectTask::where('created_by', $user->id)->where('status', 'completed')
                ->whereNotNull('completed_at')->where('completed_at', '>=', now()->subDays(7))
                ->latest('completed_at')->limit(5)->get() as $pt) {
                $push($pt->completed_at, 'fa-circle-check',
                    __('portal.notif.task_done', ['title' => $pt->title]), route('projects.manager.show', $pt->project_id));
            }
        }

        usort($items, fn ($a, $b) => $b['at'] <=> $a['at']);

        return array_slice($items, 0, $limit);
    }
}
