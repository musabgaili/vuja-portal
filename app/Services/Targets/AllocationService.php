<?php

namespace App\Services\Targets;

use App\Models\TeamMember;
use App\Models\WeeklyAllocation;
use App\Targets\Contracts\LeaveBridge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Weekly allocation capture (spec §9): the engineer confirms the % split of their
 * week across categories (must sum to 100). available_hours = capacity − leave;
 * each line's hours are cached = percent/100 × available_hours.
 */
class AllocationService
{
    public function __construct(private LeaveBridge $leave) {}

    /** Monday of the week containing $date (default: now). */
    public function weekStartFor(?Carbon $date = null): Carbon
    {
        return ($date ?? now())->copy()->startOfWeek(Carbon::MONDAY);
    }

    public function availableHours(TeamMember $member, Carbon $weekStart): float
    {
        $leave = $this->leave->leaveHours($member->id, $weekStart);

        return max(0.0, (float) $member->weekly_capacity_hours - $leave);
    }

    /** Get the member's allocation for a week, initialising a draft if none exists. */
    public function getOrInit(TeamMember $member, Carbon $weekStart): WeeklyAllocation
    {
        $allocation = WeeklyAllocation::with('lines')
            ->firstOrNew(['team_member_id' => $member->id, 'week_start' => $weekStart->copy()->startOfDay()]);

        if (! $allocation->exists) {
            $allocation->available_hours = $this->availableHours($member, $weekStart);
            $allocation->status = 'draft';
        }

        return $allocation;
    }

    /**
     * Save the week's split. $lines: [activity_category_id => percent]. Throws if
     * the percentages don't sum to 100. $submit flips draft → submitted.
     */
    public function save(TeamMember $member, Carbon $weekStart, array $lines, bool $submit): WeeklyAllocation
    {
        $weekStart = $weekStart->copy()->startOfDay();
        $clean = collect($lines)->map(fn ($p) => round((float) $p, 2))->filter(fn ($p) => $p > 0);

        if (round($clean->sum(), 2) !== 100.0) {
            throw new \RuntimeException(__('targets.cap.lines_must_sum_100'));
        }

        $available = $this->availableHours($member, $weekStart);

        return DB::transaction(function () use ($member, $weekStart, $clean, $submit, $available) {
            $allocation = WeeklyAllocation::updateOrCreate(
                ['team_member_id' => $member->id, 'week_start' => $weekStart],
                ['available_hours' => $available, 'status' => $submit ? 'submitted' : 'draft'],
            );
            $allocation->lines()->delete();
            foreach ($clean as $categoryId => $percent) {
                $allocation->lines()->create([
                    'activity_category_id' => $categoryId,
                    'percent' => $percent,
                    'hours' => round($percent / 100 * $available, 2),
                ]);
            }

            return $allocation->load('lines');
        });
    }
}
