<?php

namespace App\Services\Targets;

use App\Models\Opportunity;
use App\Models\PerformanceTarget;
use App\Models\Project;
use App\Models\Quote;
use App\Models\TargetMetric;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Computes the live actual for a metric, for a user, in a month — from existing
 * activity (auto) or the manager-entered value (manual). This is the single
 * source of "how much has this person done" used by the dashboards and the
 * points gate. No per-project time logging.
 */
class ActualsService
{
    /** @return array{0: Carbon, 1: Carbon} [startOfMonth, endOfMonth] */
    private function range(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();

        return [$start, $start->copy()->endOfMonth()];
    }

    /** Live actual for (user, metric, month). */
    public function actualFor(User $user, TargetMetric $metric, Carbon $month): float
    {
        if (! $metric->isAuto()) {
            // Manual KPI: the value the manager entered on the target row.
            return (float) (PerformanceTarget::where('user_id', $user->id)
                ->whereDate('period', $month->copy()->startOfMonth())
                ->where('target_metric_id', $metric->id)
                ->value('current_value') ?? 0);
        }

        [$start, $end] = $this->range($month);

        return match ($metric->key) {
            'quotes_produced' => (float) Quote::where('created_by', $user->id)
                ->whereNotNull('sent_at')->whereBetween('sent_at', [$start, $end])->count(),

            'projects_won' => (float) $this->projectsWon($user, $start, $end),

            'meetings_attended' => (float) \App\Models\Meeting::where('team_member_id', $user->id)
                ->where('status', 'completed')->whereBetween('completed_at', [$start, $end])->count(),

            'projects_closed' => (float) Project::where('status', 'completed')
                ->whereBetween('actual_end_date', [$start, $end])
                ->where(fn ($q) => $q->where('project_manager_id', $user->id)->orWhere('account_manager_id', $user->id))
                ->count(),

            default => 0.0,
        };
    }

    /**
     * Distinct projects won this month credited to the user via ANY role
     * (accepted-quote author, won-opportunity owner, awarded-project PM),
     * deduped by project so one deal never counts twice for the same person.
     */
    private function projectsWon(User $user, Carbon $start, Carbon $end): int
    {
        $ids = collect();

        $ids = $ids->merge(
            Quote::where('created_by', $user->id)->where('status', 'accepted')
                ->whereBetween('accepted_at', [$start, $end])->pluck('project_id')
        );
        $ids = $ids->merge(
            Opportunity::where('owner_id', $user->id)->where('stage', 'won')
                ->whereBetween('won_at', [$start, $end])->pluck('converted_project_id')
        );
        $ids = $ids->merge(
            Project::where('project_manager_id', $user->id)
                ->where('source_type', Quote::class)
                ->whereBetween('created_at', [$start, $end])->pluck('id')
        );

        return $ids->filter()->unique()->count();
    }

    public function attainment(float $actual, ?float $target): float
    {
        if ($target === null || $target <= 0) {
            return $actual > 0 ? 100.0 : 0.0;
        }

        return round($actual / $target * 100, 2);
    }

    public function rag(float $attainment): string
    {
        if ($attainment >= (float) config('targets.rag.green', 100)) {
            return 'green';
        }
        if ($attainment >= (float) config('targets.rag.amber', 70)) {
            return 'amber';
        }

        return 'red';
    }

    /**
     * One row per target the user holds this month, with live actual + RAG.
     * @return Collection<int, object>
     */
    public function summaryFor(User $user, Carbon $month): Collection
    {
        return PerformanceTarget::with('metric')
            ->where('user_id', $user->id)
            ->whereDate('period', $month->copy()->startOfMonth())
            ->get()
            ->map(function (PerformanceTarget $t) use ($user, $month) {
                $actual = $this->actualFor($user, $t->metric, $month);
                $attainment = $this->attainment($actual, (float) $t->target_value);

                return (object) [
                    'target' => $t,
                    'metric' => $t->metric,
                    'target_value' => (float) $t->target_value,
                    'actual' => $actual,
                    'attainment' => $attainment,
                    'rag' => $this->rag($attainment),
                ];
            });
    }

    /** Average attainment across the user's targets this month (drives the top-bar chip). */
    public function overallAttainment(User $user, Carbon $month): ?float
    {
        $rows = $this->summaryFor($user, $month);
        if ($rows->isEmpty()) {
            return null;
        }

        return round($rows->avg('attainment'), 0);
    }
}
