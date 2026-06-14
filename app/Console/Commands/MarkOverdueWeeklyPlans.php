<?php

namespace App\Console\Commands;

use App\Http\Controllers\WeeklyPlannerController;
use App\Models\User;
use App\Models\WeeklyPlan;
use App\Services\EngagementService;
use Illuminate\Console\Command;

/**
 * Saturday 18:01 enforcement: any internal employee who has not SUBMITTED
 * (status pending/approved) a plan for the upcoming week is marked Overdue,
 * and the late-submission IP penalty is applied once.
 */
class MarkOverdueWeeklyPlans extends Command
{
    protected $signature = 'planner:mark-overdue';

    protected $description = 'Mark un-submitted weekly plans as overdue (Saturday deadline enforcement)';

    public function handle(EngagementService $engagement): int
    {
        $weekStart = WeeklyPlannerController::upcomingWeekStart()->toDateString();
        $overdue = 0;

        foreach (User::where('type', 'internal')->get() as $user) {
            $plan = WeeklyPlan::firstOrNew(['user_id' => $user->id, 'week_start' => $weekStart]);

            // Already submitted (pending/approved) or already marked overdue → skip.
            if (in_array($plan->status, ['pending', 'approved', 'overdue'], true)) {
                continue;
            }

            $plan->status = 'overdue';
            $plan->save();
            $engagement->award($user, 'weekly_plan_late', $plan, null, 'Weekly plan deadline missed');
            $overdue++;
        }

        $this->info("Marked {$overdue} weekly plan(s) overdue for week starting {$weekStart}.");

        return self::SUCCESS;
    }
}
