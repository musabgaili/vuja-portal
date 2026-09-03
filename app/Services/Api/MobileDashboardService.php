<?php

namespace App\Services\Api;

use App\Models\Meeting;
use App\Models\ProjectTask;
use App\Models\StaffTask;
use App\Models\User;
use App\Services\ApprovalService;
use App\Services\ChatService;
use App\Services\NotificationService;
use App\Support\MobileDeepLink;

/** Role-aware home summary for the Flutter internal app. */
class MobileDashboardService
{
    public function __construct(
        private NotificationService $notifications,
        private ChatService $chat,
        private ApprovalService $approvals,
    ) {}

    public function summary(User $user): array
    {
        $staffOpen = StaffTask::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        $projectOpen = ProjectTask::query()
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $chatUnread = $this->chat->unreadCounts($user);
        $nextMeeting = $this->nextMeeting($user);

        $base = [
            'open_tasks' => $staffOpen + $projectOpen,
            'staff_tasks_open' => $staffOpen,
            'project_tasks_open' => $projectOpen,
            'unread_notifications' => $this->notifications->unreadCount($user),
            'unread_chat_messages' => $chatUnread['total'],
            'unread_chat_mentions' => $chatUnread['mentions'],
            'next_meeting' => $nextMeeting,
        ];

        if ($user->isManager() || $user->isProjectManager()) {
            $base['pending_approvals'] = $this->approvals->count($user);
        }

        if ($user->isManager()) {
            $base['stats'] = [
                'team_count' => User::query()->where('type', 'internal')->where('status', 'active')->count(),
                'active_projects' => \App\Models\Project::query()->where('status', 'active')->count(),
            ];
        }

        if ($user->holdsTargets()) {
            $base['targets_attainment'] = app(\App\Services\Targets\ActualsService::class)
                ->overallAttainment($user, now()->startOfMonth());
            if ($base['targets_attainment'] !== null) {
                $base['targets_attainment'] = (int) round($base['targets_attainment']);
            }
        }

        $base['impact_points'] = (int) $user->impact_points;

        return $base;
    }

    /** Recent activity relevant to this user (caused by them or on assigned work). */
    public function activityFeed(User $user, int $limit = 20): array
    {
        $projectIds = $user->isInternal()
            ? \App\Models\Project::query()
                ->whereHas('projectPeople', fn ($q) => $q->where('user_id', $user->id))
                ->orWhere('project_manager_id', $user->id)
                ->pluck('id')
            : collect();

        $query = \Spatie\Activitylog\Models\Activity::query()
            ->with(['causer:id,name', 'subject'])
            ->latest('id');

        $query->where(function ($q) use ($user, $projectIds) {
            $q->where('causer_id', $user->id);
            if ($projectIds->isNotEmpty()) {
                $q->orWhere(function ($q2) use ($projectIds) {
                    $q2->where('subject_type', \App\Models\Project::class)
                        ->whereIn('subject_id', $projectIds);
                });
            }
        });

        return $query->limit($limit)->get()->map(fn ($a) => [
            'id' => $a->id,
            'description' => $a->description,
            'event' => $a->event,
            'causer' => $a->causer ? ['id' => $a->causer->id, 'name' => $a->causer->name] : null,
            'subject_type' => $a->subject_type ? class_basename($a->subject_type) : null,
            'subject_id' => $a->subject_id,
            'created_at' => $a->created_at?->toIso8601String(),
        ])->all();
    }

    private function nextMeeting(User $user): ?array
    {
        $meeting = Meeting::query()
            ->where(function ($q) use ($user) {
                $q->where('team_member_id', $user->id)
                    ->orWhere('client_id', $user->id)
                    ->orWhereHas('attendees', fn ($a) => $a->where('user_id', $user->id));
            })
            ->where('scheduled_at', '>=', now())
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('scheduled_at')
            ->first();

        if (! $meeting) {
            return null;
        }

        return [
            'id' => $meeting->id,
            'title' => $meeting->title,
            'scheduled_at' => $meeting->scheduled_at?->toIso8601String(),
            'status' => $meeting->status,
            'deep_link' => MobileDeepLink::absolute('meetings/'.$meeting->id),
        ];
    }
}
