<?php

namespace App\Services\Targets;

use App\Models\PerformanceTarget;
use App\Models\TargetLog;
use App\Models\TargetMetric;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** CRUD for monthly targets, the monthly snapshot ("Record month"), and trend data. */
class TargetService
{
    public function __construct(private ActualsService $actuals) {}

    public function setTarget(User $user, TargetMetric $metric, Carbon $period, float $value, ?string $notes, ?int $by, ?float $currentValue = null): PerformanceTarget
    {
        $attrs = [
            'target_value' => $value,
            'notes' => $notes,
            'created_by' => $by,
        ];
        // Manual metrics carry a manager-entered current value.
        if (! $metric->isAuto() && $currentValue !== null) {
            $attrs['current_value'] = $currentValue;
        }

        return PerformanceTarget::updateOrCreate(
            ['user_id' => $user->id, 'period' => $period->copy()->startOfMonth(), 'target_metric_id' => $metric->id],
            $attrs,
        );
    }

    public function delete(PerformanceTarget $target): void
    {
        $target->delete();
    }

    /**
     * Snapshot a month into target_logs (the "Record month" action). Pass a user
     * to record one person, or null for everyone with a target that month.
     * Returns the number of snapshot rows written.
     */
    public function recordMonth(Carbon $period, ?User $user = null, ?int $recordedBy = null): int
    {
        $period = $period->copy()->startOfMonth();
        $query = PerformanceTarget::with('metric', 'user')->whereDate('period', $period);
        if ($user) {
            $query->where('user_id', $user->id);
        }

        $count = 0;
        foreach ($query->get() as $target) {
            if (! $target->user || ! $target->metric) {
                continue;
            }
            $actual = $this->actuals->actualFor($target->user, $target->metric, $period);
            $attainment = $this->actuals->attainment($actual, (float) $target->target_value);

            TargetLog::updateOrCreate(
                ['user_id' => $target->user_id, 'period' => $period, 'target_metric_id' => $target->target_metric_id],
                [
                    'target_value' => $target->target_value,
                    'actual_value' => $actual,
                    'attainment_pct' => $attainment,
                    'recorded_by' => $recordedBy,
                    'recorded_at' => now(),
                ],
            );
            $count++;
        }

        return $count;
    }

    /** Carry this month's targets forward to next month (the "apply defaults" action). */
    public function carryForward(Carbon $fromPeriod, ?int $by = null): int
    {
        $from = $fromPeriod->copy()->startOfMonth();
        $to = $from->copy()->addMonth();
        $count = 0;

        foreach (PerformanceTarget::whereDate('period', $from)->get() as $t) {
            PerformanceTarget::firstOrCreate(
                ['user_id' => $t->user_id, 'period' => $to, 'target_metric_id' => $t->target_metric_id],
                ['target_value' => $t->target_value, 'notes' => $t->notes, 'created_by' => $by],
            );
            $count++;
        }

        return $count;
    }

    /**
     * Trend for a user + metric over the last N months: recorded snapshots plus
     * a live point for the current (not-yet-recorded) month.
     * @return Collection<int, object> oldest → newest
     */
    public function trend(User $user, TargetMetric $metric, int $months = 6): Collection
    {
        $months = max(1, $months);
        $current = now()->startOfMonth();
        $first = $current->copy()->subMonths($months - 1);

        $logs = TargetLog::where('user_id', $user->id)
            ->where('target_metric_id', $metric->id)
            ->whereDate('period', '>=', $first)
            ->get()->keyBy(fn ($l) => $l->period->format('Y-m'));

        $points = collect();
        for ($i = 0; $i < $months; $i++) {
            $period = $first->copy()->addMonths($i);
            $key = $period->format('Y-m');
            if ($logs->has($key)) {
                $log = $logs->get($key);
                $points->push((object) [
                    'period' => $period,
                    'label' => $period->format('M'),
                    'target' => (float) $log->target_value,
                    'actual' => (float) $log->actual_value,
                    'live' => false,
                ]);
            } elseif ($key === $current->format('Y-m')) {
                // Current month not snapshotted yet — show a live point.
                $target = PerformanceTarget::where('user_id', $user->id)
                    ->where('target_metric_id', $metric->id)->whereDate('period', $period)->first();
                $points->push((object) [
                    'period' => $period,
                    'label' => $period->format('M'),
                    'target' => (float) ($target->target_value ?? 0),
                    'actual' => $target ? $this->actuals->actualFor($user, $metric, $period) : 0.0,
                    'live' => true,
                ]);
            } else {
                $points->push((object) [
                    'period' => $period,
                    'label' => $period->format('M'),
                    'target' => 0.0,
                    'actual' => 0.0,
                    'live' => false,
                ]);
            }
        }

        return $points;
    }
}
