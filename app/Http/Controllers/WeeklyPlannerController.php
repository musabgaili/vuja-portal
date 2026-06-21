<?php

namespace App\Http\Controllers;

use App\Models\EngagementLog;
use App\Models\Project;
use App\Models\ProjectPerson;
use App\Models\StaffTask;
use App\Models\WeeklyPlan;
use App\Services\EngagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WeeklyPlannerController extends Controller
{
    public function __construct(private EngagementService $engagement)
    {
    }

    /** The Sunday that starts the upcoming planned week (the default week). */
    public static function upcomingWeekStart(): Carbon
    {
        return Carbon::today()->next(Carbon::SUNDAY);
    }

    /** Saturday 18:00 the day before the planned week (the "Saturday protocol"). */
    public static function deadlineFor(Carbon $weekStart): Carbon
    {
        [$h, $m] = explode(':', (string) config('planner.deadline_time', '18:00'));

        return $weekStart->copy()->subDay()->setTime((int) $h, (int) $m);
    }

    /** Resolve the week being planned from the ?week= param, snapped to its Sunday. */
    private function resolveWeekStart(Request $request): Carbon
    {
        $week = $request->input('week');
        if ($week) {
            try {
                $d = Carbon::parse($week)->startOfDay();

                return $d->isSunday() ? $d : $d->startOfWeek(Carbon::SUNDAY);
            } catch (\Throwable $e) {
                // fall through to default
            }
        }

        return self::upcomingWeekStart();
    }

    /**
     * Find (or make) a user's plan for a week. Matches on the DATE part only —
     * week_start may be stored with a 00:00:00 time component, so an exact string
     * match (firstOrNew) would miss it and hit the unique constraint on insert.
     */
    private function planFor(int $userId, Carbon $weekStart): WeeklyPlan
    {
        return WeeklyPlan::with('lines')
            ->where('user_id', $userId)
            ->whereDate('week_start', $weekStart->toDateString())
            ->first()
            ?? new WeeklyPlan(['user_id' => $userId, 'week_start' => $weekStart->toDateString()]);
    }

    /** Employee timesheet: log hours per day against projects, tasks and activities. */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $weekStart = $this->resolveWeekStart($request);
        $plan = $this->planFor($user->id, $weekStart);

        // Rows the employee may log against: their assigned projects + direct tasks
        // + the fixed activity list. Choose-from-list only (no free text).
        $projects = Project::whereHas('projectPeople', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('title')->get();
        $tasks = StaffTask::where('assigned_to', $user->id)
            ->whereIn('status', ['open', 'in_progress', 'done'])
            ->orderByDesc('id')->get();

        // Keep rows for anything already logged, even if membership/assignment changed.
        $lineProjectIds = $plan->lines->where('kind', 'project')->pluck('project_id')->filter();
        $missingProjects = $lineProjectIds->diff($projects->pluck('id'));
        if ($missingProjects->isNotEmpty()) {
            $projects = $projects->merge(Project::whereIn('id', $missingProjects)->get());
        }
        $lineTaskIds = $plan->lines->where('kind', 'task')->pluck('staff_task_id')->filter();
        $missingTasks = $lineTaskIds->diff($tasks->pluck('id'));
        if ($missingTasks->isNotEmpty()) {
            $tasks = $tasks->merge(StaffTask::whereIn('id', $missingTasks)->get());
        }

        // cells[kind][id-or-key][day] = hours — pre-fills the grid.
        $cells = ['project' => [], 'task' => [], 'activity' => []];
        foreach ($plan->lines as $line) {
            $ref = $line->kind === 'project' ? $line->project_id
                 : ($line->kind === 'task' ? $line->staff_task_id : $line->activity);
            if ($ref !== null) {
                $cells[$line->kind][$ref] = $line->hours ?? [];
            }
        }

        return view('weekly-planner.index', [
            'plan' => $plan,
            'weekStart' => $weekStart,
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'deadline' => self::deadlineFor($weekStart),
            'days' => config('planner.days'),
            'locations' => config('planner.locations'),
            'activities' => config('planner.activities'),
            'projects' => $projects,
            'tasks' => $tasks,
            'cells' => $cells,
            'requiredHours' => (int) config('planner.required_hours', 40),
            'maxHoursPerDay' => (int) config('planner.max_hours_per_day', 24),
            'history' => WeeklyPlan::with('lines')->where('user_id', $user->id)
                ->orderByDesc('week_start')->limit(8)->get(),
        ]);
    }

    /** Save (draft) or submit the timesheet. */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $request->validate([
            'action' => 'required|in:draft,submit',
            'week' => 'nullable|date',
            'hours' => 'array',
            'locations' => 'array',
            'availability' => 'array',
        ]);

        $weekStart = $this->resolveWeekStart($request);
        $plan = $this->planFor($user->id, $weekStart);

        // Server-side lock: an approved timesheet is immutable (the UI disables
        // its inputs, but disabled fields are not a security boundary).
        if ($plan->exists && $plan->status === 'approved') {
            abort(403, 'This timesheet is approved and locked.');
        }

        $days = config('planner.days');
        $maxHpd = (int) config('planner.max_hours_per_day', 24);

        // Allowed rows = current membership/assignment ∪ anything already on the plan.
        $myProjectIds = ProjectPerson::where('user_id', $user->id)
            ->whereIn('role', ['project_manager', 'employee'])->pluck('project_id')->all();
        $myTaskIds = StaffTask::where('assigned_to', $user->id)->pluck('id')->all();
        $allowedProjectIds = array_unique(array_merge(
            $myProjectIds, $plan->lines->where('kind', 'project')->pluck('project_id')->filter()->all()
        ));
        $allowedTaskIds = array_unique(array_merge(
            $myTaskIds, $plan->lines->where('kind', 'task')->pluck('staff_task_id')->filter()->all()
        ));
        $allowedActivities = array_keys(config('planner.activities'));

        $grid = $request->input('hours', []);
        $lines = [];

        foreach (($grid['project'] ?? []) as $pid => $byDay) {
            if (! in_array((int) $pid, array_map('intval', $allowedProjectIds), true)) {
                continue;
            }
            $hours = $this->cleanHours((array) $byDay, $days, $maxHpd);
            if (array_sum($hours) <= 0) {
                continue;
            }
            $lines[] = [
                'kind' => 'project', 'project_id' => (int) $pid, 'staff_task_id' => null, 'activity' => null,
                'label' => optional(Project::find($pid))->title ?? "Project #{$pid}", 'hours' => $hours,
            ];
        }

        foreach (($grid['task'] ?? []) as $tid => $byDay) {
            if (! in_array((int) $tid, array_map('intval', $allowedTaskIds), true)) {
                continue;
            }
            $hours = $this->cleanHours((array) $byDay, $days, $maxHpd);
            if (array_sum($hours) <= 0) {
                continue;
            }
            $lines[] = [
                'kind' => 'task', 'project_id' => null, 'staff_task_id' => (int) $tid, 'activity' => null,
                'label' => optional(StaffTask::find($tid))->title ?? "Task #{$tid}", 'hours' => $hours,
            ];
        }

        foreach (($grid['activity'] ?? []) as $key => $byDay) {
            if (! in_array($key, $allowedActivities, true)) {
                continue;
            }
            $hours = $this->cleanHours((array) $byDay, $days, $maxHpd);
            if (array_sum($hours) <= 0) {
                continue;
            }
            $lines[] = [
                'kind' => 'activity', 'project_id' => null, 'staff_task_id' => null, 'activity' => $key,
                'label' => config("planner.activities.$key", $key), 'hours' => $hours,
            ];
        }

        $total = array_sum(array_map(fn ($l) => array_sum($l['hours']), $lines));

        // Clean per-day working location to the configured days/options.
        $allowedLocs = array_keys(config('planner.locations'));
        $locations = collect($request->input('locations', []))
            ->only($days)
            ->map(fn ($v) => in_array($v, $allowedLocs, true) ? $v : null)
            ->filter()
            ->toArray();

        // Clean per-day availability window { day => {start, end} } in HH:MM.
        $availability = [];
        foreach ($days as $day) {
            $start = $this->cleanTime($request->input("availability.$day.start"));
            $end = $this->cleanTime($request->input("availability.$day.end"));
            $window = array_filter(['start' => $start, 'end' => $end]);
            if ($window) {
                $availability[$day] = $window;
            }
        }

        $submitting = $request->input('action') === 'submit';
        $required = (int) config('planner.required_hours', 40);

        if ($submitting && $total !== $required) {
            return back()
                ->withErrors(['hours' => "Your timesheet must total exactly {$required} hours (currently {$total})."])
                ->withInput();
        }

        // Must-have: at least N hours on "Pre-sale Meetings" — unless the week is
        // mostly leave (vacation/unpaid/sick), which is exempt so PTO isn't blocked.
        $minPresale = (int) config('planner.min_presale_meeting_hours', 0);
        if ($submitting && $minPresale > 0) {
            $sumActivity = fn (array $keys) => array_sum(array_map(
                fn ($l) => ($l['kind'] === 'activity' && in_array($l['activity'], $keys, true)) ? array_sum($l['hours']) : 0,
                $lines,
            ));
            $presaleHours = $sumActivity(['presale_meetings']);
            $leaveHours = $sumActivity((array) config('planner.leave_activities', []));
            $leaveDominated = $total > 0 && $leaveHours >= ($total / 2);

            if ($presaleHours < $minPresale && ! $leaveDominated) {
                return back()
                    ->withErrors(['presale_meetings' => __('portal.planner.presale_min_error', ['min' => $minPresale, 'have' => (int) $presaleHours])])
                    ->withInput();
            }
        }

        // Presence is mandatory on submit: every working day with non-leave hours
        // needs a location from the list. A fully on-leave day (sick/vacation) is
        // exempt — you're not at a lab when you're on leave.
        if ($submitting) {
            $leaveKeys = (array) config('planner.leave_activities', []);
            $missing = [];
            foreach ($days as $day) {
                $workHours = 0;
                foreach ($lines as $l) {
                    $isLeave = $l['kind'] === 'activity' && in_array($l['activity'], $leaveKeys, true);
                    if (! $isLeave) {
                        $workHours += (int) ($l['hours'][$day] ?? 0);
                    }
                }
                if ($workHours > 0 && empty($locations[$day])) {
                    $missing[] = __('portal.planner.day_short.'.$day);
                }
            }
            if ($missing) {
                return back()
                    ->withErrors(['locations' => __('portal.planner.location_required', ['days' => implode(', ', $missing)])])
                    ->withInput();
            }
        }

        $wasSubmitted = in_array($plan->status, ['pending', 'approved'], true);
        $selfApprove = $user->isManager();

        if ($submitting) {
            // A manager has no approver above them, so their plan self-approves.
            $plan->status = $selfApprove ? 'approved' : 'pending';
            $plan->submitted_at = now();
            if ($selfApprove) {
                $plan->reviewed_by = $user->id;
                $plan->reviewed_at = now();
            }
        } else {
            $plan->status = $plan->status === 'rejected' ? 'rejected' : 'draft';
        }
        $plan->locations = $locations;
        $plan->availability = $availability;

        DB::transaction(function () use ($plan, $lines) {
            $plan->save();
            $plan->lines()->delete();
            foreach ($lines as $l) {
                $plan->lines()->create($l);
            }
        });

        // Reward the first on-time/late submission, once. The existence check
        // makes this idempotent even under a rapid double-submit (no duplicate IP).
        if ($submitting && ! $wasSubmitted) {
            $alreadyAwarded = EngagementLog::where('subject_type', WeeklyPlan::class)
                ->where('subject_id', $plan->id)
                ->whereIn('action', ['weekly_plan_on_time', 'weekly_plan_late'])
                ->exists();
            if (! $alreadyAwarded) {
                $onTime = now()->lessThanOrEqualTo(self::deadlineFor($weekStart));
                $this->engagement->award($user, $onTime ? 'weekly_plan_on_time' : 'weekly_plan_late', $plan, null, 'Weekly plan submitted');
            }
        }

        $msg = $submitting
            ? ($selfApprove ? 'Timesheet submitted and auto-approved (no approver above a manager).' : 'Timesheet submitted for approval.')
            : 'Draft saved.';

        return redirect()->route('weekly-planner.index', ['week' => $weekStart->toDateString()])->with('success', $msg);
    }

    /** Clamp/normalise a {day => hours} map to the configured days and cap. */
    private function cleanHours(array $byDay, array $days, int $max): array
    {
        $out = [];
        foreach ($days as $day) {
            $v = (int) ($byDay[$day] ?? 0);
            $v = max(0, min($max, $v));
            if ($v > 0) {
                $out[$day] = $v;
            }
        }

        return $out;
    }

    /** Validate a HH:MM (24h) time string, else null. */
    private function cleanTime(?string $value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) ? $value : null;
    }

    /** Manager review queue + category breakdown across submitted timesheets. */
    public function review(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isManager(), 403);

        $weekStart = $this->resolveWeekStart($request);
        $plans = WeeklyPlan::with(['user', 'lines'])->whereDate('week_start', $weekStart->toDateString())->get();

        $breakdown = [];
        foreach ($plans->whereIn('status', ['pending', 'approved']) as $plan) {
            foreach ($plan->categoryBreakdown() as $cat => $h) {
                $breakdown[$cat] = ($breakdown[$cat] ?? 0) + $h;
            }
        }
        arsort($breakdown);

        $days = config('planner.days');
        $locations = config('planner.locations');

        return view('weekly-planner.review', array_merge([
            'plans' => $plans,
            'pending' => $plans->where('status', 'pending'),
            'breakdown' => $breakdown,
            'breakdownTotal' => array_sum($breakdown),
            'days' => $days,
            'locations' => $locations,
            // High-level matrix: every employee with a plan, their daily location + hours.
            'overview' => $this->weekOverview($plans, $days, $locations),
        ], $this->weekNav($weekStart)));
    }

    /** Week-navigation context (label + prev/this/next) shared by review + presence. */
    private function weekNav(Carbon $weekStart): array
    {
        return [
            'weekStart' => $weekStart,
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'thisWeek' => Carbon::today()->startOfWeek(Carbon::SUNDAY)->toDateString(),
            'isCurrentWeek' => $weekStart->isSameDay(Carbon::today()->startOfWeek(Carbon::SUNDAY)),
        ];
    }

    /** Read-only detail of a single weekly plan (the click-through target). */
    public function show(WeeklyPlan $weeklyPlan)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        // Employees may only open their own plan; managers/PMs may open any.
        if (! $user->isManager() && ! $user->isProjectManager() && $weeklyPlan->user_id !== $user->id) {
            abort(403);
        }

        $weeklyPlan->load(['user', 'lines']);

        return view('weekly-planner.show', [
            'plan' => $weeklyPlan,
            'days' => config('planner.days'),
            'locations' => config('planner.locations'),
        ]);
    }

    public function approve(WeeklyPlan $weeklyPlan)
    {
        $user = Auth::user();
        abort_unless($user->isManager(), 403);

        $weeklyPlan->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);
        $this->engagement->award($weeklyPlan->user, 'weekly_plan_approved', $weeklyPlan, null, 'Weekly plan approved');

        return back()->with('success', "Approved {$weeklyPlan->user->name}'s plan.");
    }

    public function reject(Request $request, WeeklyPlan $weeklyPlan)
    {
        $user = Auth::user();
        abort_unless($user->isManager(), 403);

        $validated = $request->validate(['review_notes' => 'nullable|string|max:500']);
        $weeklyPlan->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return back()->with('success', "Sent back {$weeklyPlan->user->name}'s plan for changes.");
    }

    /**
     * Team Presence: who is where, per day, for the upcoming week.
     *
     * Visible to all internal staff. Employees and PMs only see approved plans
     * (high-level), managers also see pending ones. Project managers additionally
     * get a breakdown of who is allocated to the projects/tasks they manage.
     */
    public function presence(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $isManager = $user->isManager();
        $weekStart = $this->resolveWeekStart($request);

        $statuses = $isManager ? ['pending', 'approved'] : ['approved'];
        $plans = WeeklyPlan::with(['user', 'lines'])->whereDate('week_start', $weekStart->toDateString())
            ->whereIn('status', $statuses)->get();

        $days = config('planner.days');
        $locations = config('planner.locations');

        // grid[day][locationKey] = [names...]
        $grid = [];
        foreach ($days as $day) {
            foreach (array_keys($locations) as $loc) {
                $grid[$day][$loc] = [];
            }
            foreach ($plans as $plan) {
                $loc = $plan->locations[$day] ?? null;
                if ($loc && isset($grid[$day][$loc])) {
                    $grid[$day][$loc][] = $plan->user->name;
                }
            }
        }

        // PMs (and managers) see allocation detail for the projects/tasks they own.
        $managedAllocation = null;
        if ($user->isProjectManager() || $isManager) {
            $managedAllocation = $this->managedAllocation($user, $plans);
        }

        return view('weekly-planner.presence', array_merge([
            'grid' => $grid,
            'days' => $days,
            'locations' => $locations,
            'overview' => $this->weekOverview($plans, $days, $locations),
            'managedAllocation' => $managedAllocation,
            'canDrillIn' => $isManager || $user->isProjectManager(),
        ], $this->weekNav($weekStart)));
    }

    /**
     * Build the employee × day matrix: each employee's working location and total
     * logged hours per day, plus the weekly total. Sorted by name.
     *
     * @return array<int, array{plan: WeeklyPlan, name: string, days: array, total: int, status: string}>
     */
    private function weekOverview($plans, array $days, array $locations): array
    {
        $leaveKeys = (array) config('planner.leave_activities', []);
        $rows = [];
        foreach ($plans as $plan) {
            $perDay = [];
            foreach ($days as $day) {
                $hours = 0;
                $leaveHours = 0;
                $leaveKey = null;
                foreach ($plan->lines as $line) {
                    $h = (int) (($line->hours[$day] ?? 0));
                    $hours += $h;
                    if ($h > 0 && $line->kind === 'activity' && in_array($line->activity, $leaveKeys, true)) {
                        $leaveHours += $h;
                        $leaveKey = $leaveKey ?: $line->activity;
                    }
                }
                $locKey = $plan->locations[$day] ?? null;
                $win = $plan->availability[$day] ?? null;
                // A leave-dominated day shows the leave reason instead of a work location.
                $isLeaveDay = $hours > 0 && $leaveHours >= ($hours / 2);
                $leaveLabel = $leaveKey
                    ? (\Illuminate\Support\Facades\Lang::has('portal.planner.cat.'.$leaveKey)
                        ? __('portal.planner.cat.'.$leaveKey)
                        : config('planner.activities.'.$leaveKey, $leaveKey))
                    : null;
                $perDay[$day] = [
                    'loc' => $locKey
                        ? (\Illuminate\Support\Facades\Lang::has('portal.planner.location.'.$locKey)
                            ? __('portal.planner.location.'.$locKey)
                            : ($locations[$locKey] ?? $locKey))
                        : null,
                    'hours' => $hours,
                    'window' => ($win && ($win['start'] ?? null) && ($win['end'] ?? null)) ? ($win['start'].'–'.$win['end']) : null,
                    'leave' => $isLeaveDay ? $leaveLabel : null,
                ];
            }
            $rows[] = [
                'plan' => $plan,
                'name' => $plan->user->name,
                'days' => $perDay,
                'total' => $plan->totalHours(),
                'status' => $plan->status,
                'late' => $plan->isLate(),
            ];
        }

        usort($rows, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $rows;
    }

    /**
     * For a project manager, attribute this week's logged project hours to the
     * team members working on the projects that PM manages.
     *
     * @return array<string, array<int, array{name: string, hours: int}>>
     */
    private function managedAllocation($user, $plans): array
    {
        $managedProjectIds = ProjectPerson::where('user_id', $user->id)
            ->where('role', 'project_manager')->pluck('project_id')->map(fn ($id) => (int) $id)->all();

        if (empty($managedProjectIds)) {
            return [];
        }

        $out = [];
        foreach ($plans as $plan) {
            foreach ($plan->lines as $line) {
                if ($line->kind === 'project' && in_array((int) $line->project_id, $managedProjectIds, true)) {
                    $title = $line->label ?: ('Project #'.$line->project_id);
                    $out[$title][] = ['name' => $plan->user->name, 'hours' => $line->totalHours()];
                }
            }
        }

        return $out;
    }
}
