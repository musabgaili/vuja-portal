<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WeeklyPlan;
use App\Services\EngagementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyPlannerController extends Controller
{
    public function __construct(private EngagementService $engagement)
    {
    }

    /** The Sunday that starts the upcoming planned week. */
    public static function upcomingWeekStart(): Carbon
    {
        return Carbon::today()->next(Carbon::SUNDAY);
    }

    /** Saturday 18:00 the day before the planned week. */
    public static function deadlineFor(Carbon $weekStart): Carbon
    {
        [$h, $m] = explode(':', (string) config('planner.deadline_time', '18:00'));

        return $weekStart->copy()->subDay()->setTime((int) $h, (int) $m);
    }

    /** Employee view: create / edit my plan for the upcoming week. */
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $weekStart = self::upcomingWeekStart();
        $plan = WeeklyPlan::firstOrNew(['user_id' => $user->id, 'week_start' => $weekStart->toDateString()]);

        return view('weekly-planner.index', [
            'plan' => $plan,
            'weekStart' => $weekStart,
            'deadline' => self::deadlineFor($weekStart),
            'buckets' => config('planner.buckets'),
            'days' => config('planner.days'),
            'locations' => config('planner.locations'),
            'requiredHours' => (int) config('planner.required_hours', 40),
            'history' => WeeklyPlan::where('user_id', $user->id)->orderByDesc('week_start')->limit(8)->get(),
        ]);
    }

    /** Save (draft) or submit the plan. */
    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $validated = $request->validate([
            'hours_projects' => 'required|integer|min:0|max:80',
            'hours_development' => 'required|integer|min:0|max:80',
            'hours_presale' => 'required|integer|min:0|max:80',
            'locations' => 'array',
            'action' => 'required|in:draft,submit',
        ]);

        $weekStart = self::upcomingWeekStart();
        $plan = WeeklyPlan::firstOrNew(['user_id' => $user->id, 'week_start' => $weekStart->toDateString()]);

        // Clean locations to the configured days/options.
        $allowedDays = config('planner.days');
        $allowedLocs = array_keys(config('planner.locations'));
        $locations = collect($request->input('locations', []))
            ->only($allowedDays)
            ->map(fn ($v) => in_array($v, $allowedLocs, true) ? $v : null)
            ->toArray();

        $plan->fill([
            'hours_projects' => $validated['hours_projects'],
            'hours_development' => $validated['hours_development'],
            'hours_presale' => $validated['hours_presale'],
            'locations' => $locations,
        ]);

        if ($validated['action'] === 'submit') {
            $required = (int) config('planner.required_hours', 40);
            if ($plan->totalHours() !== $required) {
                return back()->withErrors(['hours' => "Your allocation must total exactly {$required} hours (currently {$plan->totalHours()})."])->withInput();
            }
            $wasSubmitted = $plan->status === 'pending' || $plan->status === 'approved';

            // A manager has no approver above them, so their own plan self-approves
            // (it never enters anyone's pending queue). Everyone else awaits review.
            $selfApprove = $user->isManager();
            $plan->status = $selfApprove ? 'approved' : 'pending';
            $plan->submitted_at = now();
            if ($selfApprove) {
                $plan->reviewed_by = $user->id;
                $plan->reviewed_at = now();
            }
            $plan->save();

            // Engagement: reward on-time submission, penalise a late one (once).
            if (! $wasSubmitted) {
                $onTime = now()->lessThanOrEqualTo(self::deadlineFor($weekStart));
                $this->engagement->award($user, $onTime ? 'weekly_plan_on_time' : 'weekly_plan_late', $plan, null, 'Weekly plan submitted');
            }

            return redirect()->route('weekly-planner.index')
                ->with('success', $selfApprove ? 'Weekly plan submitted and auto-approved (no approver above a manager).' : 'Weekly plan submitted for approval.');
        }

        $plan->status = $plan->status === 'rejected' ? 'rejected' : 'draft';
        $plan->save();

        return redirect()->route('weekly-planner.index')->with('success', 'Draft saved.');
    }

    /** Manager review queue + Time-Value breakdown. */
    public function review(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isManager(), 403);

        $weekStart = self::upcomingWeekStart()->toDateString();
        $plans = WeeklyPlan::with('user')->where('week_start', $weekStart)->get();

        // Time-Value totals across all submitted plans.
        $submitted = $plans->whereIn('status', ['pending', 'approved']);
        $timeValue = [
            'projects' => $submitted->sum('hours_projects'),
            'development' => $submitted->sum('hours_development'),
            'presale' => $submitted->sum('hours_presale'),
        ];

        return view('weekly-planner.review', [
            'weekStart' => Carbon::parse($weekStart),
            'plans' => $plans,
            'pending' => $plans->where('status', 'pending'),
            'timeValue' => $timeValue,
            'buckets' => config('planner.buckets'),
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

    /** Team Presence: who is where, per day, for the upcoming week. */
    public function presence()
    {
        $user = Auth::user();
        abort_unless($user->isManager(), 403);

        $weekStart = self::upcomingWeekStart()->toDateString();
        $plans = WeeklyPlan::with('user')->where('week_start', $weekStart)
            ->whereIn('status', ['pending', 'approved'])->get();

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

        return view('weekly-planner.presence', [
            'weekStart' => Carbon::parse($weekStart),
            'grid' => $grid,
            'days' => $days,
            'locations' => $locations,
        ]);
    }
}
