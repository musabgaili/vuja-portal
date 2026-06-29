<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityCategory;
use App\Models\LeaveEntry;
use App\Models\Project;
use App\Models\ProjectAssignment;
use App\Models\Quote;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\WeeklyAllocation;
use App\Services\Targets\AllocationService;
use App\Services\Targets\CapacityActualsService;
use App\Targets\Contracts\ProjectsBridge;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Manager capacity control room (spec §13, §14): team utilization + allocation
 * heatmap + delivery-vs-pre-sales balance, plus config for engineers/skills,
 * leave, project assignments (revenue attribution), and weekly submissions.
 */
class CapacityController extends Controller
{
    public function __construct(
        private CapacityActualsService $actuals,
        private AllocationService $alloc,
        private ProjectsBridge $projects,
    ) {}

    private function guard(): void
    {
        abort_unless(Gate::allows('manage-targets'), 403);
    }

    private function resolveMonth(Request $request): Carbon
    {
        if ($request->filled('month')) {
            try {
                return Carbon::createFromFormat('Y-m', $request->get('month'))->startOfMonth();
            } catch (\Throwable $e) {
            }
        }

        return now()->startOfMonth();
    }

    public function dashboard(Request $request)
    {
        $this->guard();
        $month = $this->resolveMonth($request);
        $categories = ActivityCategory::active()->orderBy('sort_order')->get();
        $members = TeamMember::active()->with('user')->get();

        $rows = $members->map(fn (TeamMember $m) => (object) [
            'member' => $m,
            'available' => $this->actuals->availableHours($m, $month),
            'utilization' => $this->actuals->utilization($m, $month),
            'alloc' => $this->actuals->allocationByCategory($m, $month),
            'revenue' => $this->actuals->revenue($m, $month)['total'],
            'output' => $this->actuals->output($m, $month),
        ]);

        return view('capacity.admin.dashboard', [
            'rows' => $rows,
            'categories' => $categories,
            'month' => $month,
            'utilTarget' => $this->actuals->utilizationTarget(),
        ]);
    }

    // ---- Engineers + skills ----

    public function engineers()
    {
        $this->guard();

        return view('capacity.admin.engineers', [
            'members' => TeamMember::with('user', 'categories')->get(),
            'deliveryCategories' => ActivityCategory::active()->where('kind', 'delivery')->orderBy('sort_order')->get(),
            'candidates' => User::where('type', 'internal')->whereIn('role', ['employee', 'project_manager'])
                ->whereDoesntHave('teamMember')->orderBy('name')->get(),
        ]);
    }

    public function storeEngineer(Request $request)
    {
        $this->guard();
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id']);
        $user = User::findOrFail($data['user_id']);
        abort_unless($user->isInternal(), 422);

        TeamMember::firstOrCreate(['user_id' => $user->id], [
            'display_name' => $user->name,
            'weekly_capacity_hours' => (float) config('targets.default_weekly_capacity', 40),
            'min_weekly_hours' => (float) config('targets.default_weekly_capacity', 40),
            'is_active' => true,
        ]);

        return back()->with('success', __('targets.cap.engineer_saved'));
    }

    public function updateEngineer(Request $request, TeamMember $teamMember)
    {
        $this->guard();
        $data = $request->validate([
            // The manager-set floor; the engineer's committed hours can't fall below it.
            'min_weekly_hours' => 'required|numeric|min:0|max:168',
            'weekly_capacity_hours' => 'required|numeric|min:0|max:168|gte:min_weekly_hours',
            'specialization' => 'nullable|in:design,electronics',
            'skills' => 'nullable|array',
            // Skills are the DELIVERY categories an engineer covers (spec §7/§12).
            'skills.*' => ['integer', Rule::exists('activity_categories', 'id')->where('kind', 'delivery')->where('is_active', true)],
        ]);

        $teamMember->update([
            'min_weekly_hours' => $data['min_weekly_hours'],
            'weekly_capacity_hours' => $data['weekly_capacity_hours'],
            'specialization' => $data['specialization'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);
        // Sync the skill map (delivery categories the engineer covers).
        $teamMember->categories()->sync($data['skills'] ?? []);

        return back()->with('success', __('targets.cap.engineer_saved'));
    }

    // ---- Leave ----

    public function leave()
    {
        $this->guard();

        return view('capacity.admin.leave', [
            'members' => TeamMember::active()->with('user')->get(),
            'entries' => LeaveEntry::with('member.user')->latest('date')->paginate(30),
        ]);
    }

    public function storeLeave(Request $request)
    {
        $this->guard();
        $data = $request->validate([
            'team_member_id' => 'required|integer|exists:team_members,id',
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0|max:24',
            'type' => 'required|in:leave,holiday',
        ]);
        LeaveEntry::create($data);

        return back()->with('success', __('targets.cap.leave_saved'));
    }

    public function deleteLeave(LeaveEntry $leaveEntry)
    {
        $this->guard();
        $leaveEntry->delete();

        return back()->with('success', __('targets.cap.leave_removed'));
    }

    // ---- Project assignments (revenue attribution) ----

    public function assignments(Request $request)
    {
        $this->guard();
        $selected = $request->filled('project_id') ? Project::find($request->get('project_id')) : null;

        return view('capacity.admin.assignments', [
            'projects' => Project::where('source_type', Quote::class)->latest()->take(100)->get(),
            'selected' => $selected,
            'lines' => $selected ? $this->projects->assignableLines($selected->id) : collect(),
            'existing' => $selected ? ProjectAssignment::with('member.user', 'category')->where('project_id', $selected->id)->get() : collect(),
            'members' => TeamMember::active()->with('user')->get(),
            'categories' => ActivityCategory::active()->where('kind', 'delivery')->orderBy('sort_order')->get(),
        ]);
    }

    public function storeAssignment(Request $request)
    {
        $this->guard();
        $data = $request->validate([
            'team_member_id' => 'required|integer|exists:team_members,id',
            'project_id' => 'required|integer|exists:projects,id',
            'project_line_id' => 'nullable|integer',
            'activity_category_id' => 'required|integer|exists:activity_categories,id',
            'value' => 'required|numeric|min:0|max:100000000',
        ]);
        // Dedupe: one assignment per (project, line, engineer, category) — a repeat
        // updates the value rather than double-counting recognized revenue.
        ProjectAssignment::updateOrCreate(
            [
                'project_id' => $data['project_id'],
                'project_line_id' => $data['project_line_id'] ?? null,
                'team_member_id' => $data['team_member_id'],
                'activity_category_id' => $data['activity_category_id'],
            ],
            ['value' => $data['value']],
        );

        return back()->with('success', __('targets.cap.assignment_saved'));
    }

    public function deleteAssignment(ProjectAssignment $assignment)
    {
        $this->guard();
        $projectId = $assignment->project_id;
        $assignment->delete();

        return back()->with('success', __('targets.cap.assignment_removed'));
    }

    // ---- Weekly submission monitor ----

    public function submissions()
    {
        $this->guard();
        $week = $this->alloc->weekStartFor();
        $submitted = WeeklyAllocation::where('week_start', $week)->get()->keyBy('team_member_id');

        return view('capacity.admin.submissions', [
            'members' => TeamMember::active()->with('user')->get(),
            'submitted' => $submitted,
            'week' => $week,
        ]);
    }
}
