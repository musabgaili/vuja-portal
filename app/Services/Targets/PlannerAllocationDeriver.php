<?php

namespace App\Services\Targets;

use App\Models\ActivityCategory;
use App\Models\TeamMember;
use App\Models\WeeklyAllocation;
use App\Models\WeeklyPlan;
use Illuminate\Support\Facades\DB;

/**
 * Turns a submitted Weekly Plan into a capacity WeeklyAllocation, so the manager
 * utilization dashboard stays fed from the planner (the single place employees
 * enter their week). Coarse but consistent mapping:
 *   - projects + tasks  → billable delivery (spread across the member's skills),
 *   - pre-sale activities → pre_sales,
 *   - other non-leave activities → internal,
 *   - leave (vacation/sick) is excluded from available hours.
 */
class PlannerAllocationDeriver
{
    private const PRESALE_ACTIVITIES = ['presale_meetings', 'presales'];

    public function derive(TeamMember $member, WeeklyPlan $plan): void
    {
        $leaveKeys = (array) config('planner.leave_activities', []);

        $delivery = 0.0;
        $presale = 0.0;
        $internal = 0.0;

        foreach ($plan->lines as $line) {
            $hrs = (float) $line->totalHours();
            if ($hrs <= 0) {
                continue;
            }

            if ($line->kind === 'project' || $line->kind === 'task') {
                $delivery += $hrs;
            } elseif ($line->kind === 'activity') {
                if (in_array($line->activity, $leaveKeys, true)) {
                    continue; // leave doesn't count toward worked/available hours
                }
                if (in_array($line->activity, self::PRESALE_ACTIVITIES, true)) {
                    $presale += $hrs;
                } else {
                    $internal += $hrs;
                }
            }
        }

        $available = round($delivery + $presale + $internal, 2);
        $weekStart = $plan->week_start->copy()->startOfDay();

        DB::transaction(function () use ($member, $weekStart, $available, $delivery, $presale, $internal) {
            $allocation = WeeklyAllocation::updateOrCreate(
                ['team_member_id' => $member->id, 'week_start' => $weekStart],
                ['available_hours' => $available, 'status' => 'submitted'],
            );
            $allocation->lines()->delete();

            $put = function (?int $categoryId, float $hours) use ($allocation, $available) {
                if (! $categoryId || $hours <= 0) {
                    return;
                }
                $allocation->lines()->create([
                    'activity_category_id' => $categoryId,
                    'percent' => $available > 0 ? round($hours / $available * 100, 2) : 0,
                    'hours' => round($hours, 2),
                ]);
            };

            // Delivery → the member's delivery skill categories (even split); if they
            // have none, fall back to the first active billable delivery category so
            // utilization still counts the billable hours.
            if ($delivery > 0) {
                $skillIds = $member->categories()
                    ->where('kind', 'delivery')->pluck('activity_categories.id')->all();
                if (empty($skillIds)) {
                    $fallback = ActivityCategory::where('kind', 'delivery')->where('is_active', true)
                        ->orderBy('sort_order')->first();
                    $skillIds = $fallback ? [$fallback->id] : [];
                }
                if (! empty($skillIds)) {
                    $each = $delivery / count($skillIds);
                    foreach ($skillIds as $id) {
                        $put((int) $id, $each);
                    }
                }
            }

            if ($presale > 0 && ($pre = ActivityCategory::byKey('pre_sales'))) {
                $put($pre->id, $presale);
            }
            if ($internal > 0 && ($int = ActivityCategory::byKey('internal'))) {
                $put($int->id, $internal);
            }
        });
    }
}
