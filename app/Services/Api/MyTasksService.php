<?php

namespace App\Services\Api;

use App\Models\ProjectTask;
use App\Models\StaffTask;
use App\Models\User;
use App\Services\EngagementService;
use App\Support\MobileDeepLink;
use Illuminate\Support\Collection;

/** Unified inbox: direct staff tasks + project kanban tasks assigned to the user. */
class MyTasksService
{
    /**
     * @return array{items: list<array<string,mixed>>, meta: array<string,int|null>}
     */
    public function list(User $user, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $staffQuery = StaffTask::query()
            ->with(['project:id,title', 'assigner:id,name'])
            ->where('assigned_to', $user->id);

        if ($status && in_array($status, StaffTask::STATUSES, true)) {
            $staffQuery->where('status', $status);
        } elseif ($status === 'open') {
            $staffQuery->whereNotIn('status', ['done', 'cancelled']);
        }

        $projectQuery = ProjectTask::query()
            ->with(['project:id,title', 'milestone:id,title'])
            ->where('assigned_to', $user->id);

        if ($status === 'open') {
            $projectQuery->whereNotIn('status', ['completed', 'cancelled']);
        } elseif ($status && $status !== 'open') {
            $map = [
                'in_progress' => 'in_progress',
                'done' => 'completed',
                'completed' => 'completed',
                'todo' => 'todo',
                'review' => 'review',
                'blocked' => 'blocked',
            ];
            if (isset($map[$status])) {
                $projectQuery->where('status', $map[$status]);
            }
        }

        $items = collect()
            ->merge($staffQuery->get()->map(fn (StaffTask $t) => $this->formatStaffTask($t)))
            ->merge($projectQuery->get()->map(fn (ProjectTask $t) => $this->formatProjectTask($t)))
            ->sortByDesc('updated_at')
            ->values();

        return $this->paginateCollection($items, $page, $perPage);
    }

    public function updateStaffTaskStatus(StaffTask $task, User $user, string $status): StaffTask
    {
        abort_unless($user->isManager() || $task->assigned_to === $user->id, 403);
        abort_unless(in_array($status, StaffTask::STATUSES, true), 422);

        $engagement = app(EngagementService::class);
        $notifier = app(\App\Services\Notifier::class);

        $task->status = $status;

        if ($status === 'done') {
            $task->load('assignee', 'assigner');
            $task->completed_at = $task->completed_at ?? now();
            $firstAward = ! $task->points_awarded;

            if ($firstAward) {
                $engagement->award(
                    $task->assignee,
                    $task->engagementAction(),
                    $task,
                    null,
                    'Direct task completed: '.$task->title,
                );
                $task->points_awarded = true;
            }

            if ($firstAward && $task->assigner && $task->assigner->id !== $user->id) {
                $notifier->email(
                    $task->assigner, 'task_done',
                    __('portal.notif_prefs.mail.task_done_subject'),
                    __('portal.notif_prefs.mail.task_done_heading'),
                    __('portal.notif_prefs.mail.task_done_body', ['title' => $task->title, 'by' => $user->name]),
                    route('staff-tasks.index'),
                );
            }
        } elseif ($status !== 'done') {
            $task->completed_at = null;
        }

        $task->save();

        return $task->fresh(['project:id,title', 'assigner:id,name']);
    }

    public function updateProjectTaskStatus(ProjectTask $task, User $user, string $status): ProjectTask
    {
        abort_unless($task->assigned_to === $user->id || $user->canManageProjects(), 403);

        $allowed = ['todo', 'in_progress', 'review', 'completed', 'blocked', 'cancelled'];
        abort_unless(in_array($status, $allowed, true), 422);

        $task->status = $status;
        if ($status === 'completed') {
            $task->completed_at = $task->completed_at ?? now();
        } else {
            $task->completed_at = null;
        }
        $task->save();

        return $task->fresh(['project:id,title']);
    }

    private function formatStaffTask(StaffTask $t): array
    {
        return [
            'kind' => 'staff',
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'status' => $t->status,
            'priority' => $t->priority,
            'category' => $t->category,
            'due_date' => $t->due_date?->toDateString(),
            'is_overdue' => $t->isOverdue(),
            'project' => $t->project ? ['id' => $t->project->id, 'title' => $t->project->title] : null,
            'assigner' => $t->assigner ? ['id' => $t->assigner->id, 'name' => $t->assigner->name] : null,
            'updated_at' => $t->updated_at?->toIso8601String(),
            'deep_link' => MobileDeepLink::absolute('tasks/staff/'.$t->id),
        ];
    }

    private function formatProjectTask(ProjectTask $t): array
    {
        return [
            'kind' => 'project',
            'id' => $t->id,
            'title' => $t->title,
            'description' => $t->description,
            'status' => $t->status,
            'priority' => $t->priority,
            'due_date' => $t->due_date?->toDateString(),
            'project' => $t->project ? ['id' => $t->project->id, 'title' => $t->project->title] : null,
            'milestone' => $t->milestone ? ['id' => $t->milestone->id, 'title' => $t->milestone->title] : null,
            'updated_at' => $t->updated_at?->toIso8601String(),
            'deep_link' => $t->project
                ? MobileDeepLink::absolute('projects/'.$t->project_id.'/tasks/'.$t->id)
                : null,
        ];
    }

    /**
     * @param  Collection<int, array<string,mixed>>  $items
     * @return array{items: list<array<string,mixed>>, meta: array<string,int|null>}
     */
    private function paginateCollection(Collection $items, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $total = $items->count();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values()->all();

        return [
            'items' => $slice,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int) max(1, ceil($total / $perPage)),
            ],
        ];
    }
}
