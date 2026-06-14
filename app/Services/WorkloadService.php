<?php

namespace App\Services;

use App\Models\ConsultationRequest;
use App\Models\CopyrightRegistration;
use App\Models\IdeaRequest;
use App\Models\IpRegistration;
use App\Models\Project;
use App\Models\ProjectPerson;
use App\Models\ProjectTask;
use App\Models\ResearchRequest;
use App\Models\StaffTask;
use App\Models\User;

/**
 * Employee workload — powers the manager's "who is busy / who is idle" heatmap.
 * Load = active projects (as PM) + open tasks + open assigned service requests.
 */
class WorkloadService
{
    private const DONE = ['completed', 'closed', 'cancelled', 'done', 'delivered', 'rejected', 'lost'];

    public function grid()
    {
        $employees = User::where('type', 'internal')->orderBy('name')->get();

        $rows = $employees->map(function (User $u) {
            $projects = Project::where('project_manager_id', $u->id)
                ->whereIn('status', ProjectHealthService::ACTIVE_STATUSES)->count();

            $tasks = ProjectTask::where('assigned_to', $u->id)
                ->where('status', '!=', 'completed')->count();

            $requests = 0;
            foreach ([ConsultationRequest::class, IdeaRequest::class, ResearchRequest::class, IpRegistration::class, CopyrightRegistration::class] as $model) {
                $requests += $model::where('assigned_to', $u->id)->whereNotIn('status', self::DONE)->count();
            }

            $load = $projects * 2 + $tasks + $requests;

            return [
                'user' => $u,
                'projects' => $projects,
                'tasks' => $tasks,
                'requests' => $requests,
                'load' => $load,
                'capacity' => $this->capacity($load),
            ];
        })->sortByDesc('load')->values();

        return [
            'rows' => $rows,
            'max' => max(1, (int) $rows->max('load')),
            'maxProjects' => max(1, (int) $rows->max('projects')),
            'maxTasks' => max(1, (int) $rows->max('tasks')),
            'maxRequests' => max(1, (int) $rows->max('requests')),
        ];
    }

    /** Full workload detail for one employee — powers the manager drill-down. */
    public function employeeDetail(User $user): array
    {
        $memberships = ProjectPerson::where('user_id', $user->id)->with('project')->get()
            ->filter(fn ($pp) => $pp->project)->values();

        $projectTasks = ProjectTask::where('assigned_to', $user->id)
            ->where('status', '!=', 'completed')->with('project')->latest()->get();

        $staffTasks = StaffTask::where('assigned_to', $user->id)
            ->where('status', '!=', 'cancelled')->with('project')->latest()->get();

        $projectsManaged = Project::where('project_manager_id', $user->id)
            ->whereIn('status', ProjectHealthService::ACTIVE_STATUSES)->count();

        $requests = 0;
        foreach ([ConsultationRequest::class, IdeaRequest::class, ResearchRequest::class, IpRegistration::class, CopyrightRegistration::class] as $model) {
            $requests += $model::where('assigned_to', $user->id)->whereNotIn('status', self::DONE)->count();
        }

        $load = $projectsManaged * 2 + $projectTasks->count() + $requests;

        return [
            'memberships' => $memberships,
            'projectTasks' => $projectTasks,
            'staffTasks' => $staffTasks,
            'requests' => $requests,
            'load' => $load,
            'capacity' => $this->capacity($load),
            // distinct projects the employee belongs to — targets for quick-add
            'memberProjects' => $memberships->pluck('project')->filter()->unique('id')->sortBy('title')->values(),
        ];
    }

    private function capacity(int $load): string
    {
        return match (true) {
            $load === 0 => 'idle',
            $load <= 3 => 'light',
            $load <= 7 => 'moderate',
            $load <= 12 => 'busy',
            default => 'overloaded',
        };
    }
}
