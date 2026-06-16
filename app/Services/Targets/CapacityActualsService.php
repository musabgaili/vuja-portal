<?php

namespace App\Services\Targets;

use App\Models\ActivityCategory;
use App\Models\TeamMember;
use App\Models\WeeklyAllocation;
use App\Targets\Contracts\ProjectsBridge;
use App\Targets\Contracts\QuotesBridge;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Monthly rollups for the capacity layer (spec §10): allocation % per category
 * and utilization % from submitted weekly logs; revenue from the projects bridge;
 * pre-sales output from the quotes bridge. Allocation/utilization use SUBMITTED
 * weeks only (a draft isn't a confirmed log).
 */
class CapacityActualsService
{
    public function __construct(private ProjectsBridge $projects, private QuotesBridge $quotes) {}

    /** Per-request memos so the dashboard doesn't re-query per metric per engineer. */
    private array $allocCache = [];

    private ?array $billableIds = null;

    /** @return Collection<int, WeeklyAllocation> submitted allocations whose week starts in the month */
    public function monthAllocations(TeamMember $member, Carbon $month): Collection
    {
        $key = $member->id.'|'.$month->format('Y-m');
        if (isset($this->allocCache[$key])) {
            return $this->allocCache[$key];
        }
        $start = $month->copy()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->allocCache[$key] = WeeklyAllocation::with('lines')
            ->where('team_member_id', $member->id)
            ->where('status', 'submitted')
            ->whereBetween('week_start', [$start, $end])
            ->get();
    }

    private function billableCategoryIds(): array
    {
        return $this->billableIds ??= ActivityCategory::where('is_billable', true)->pluck('id')->all();
    }

    public function availableHours(TeamMember $member, Carbon $month): float
    {
        return (float) $this->monthAllocations($member, $month)->sum('available_hours');
    }

    /** @return array<int, array{hours: float, pct: float}> keyed by activity_category_id */
    public function allocationByCategory(TeamMember $member, Carbon $month): array
    {
        $allocations = $this->monthAllocations($member, $month);
        $available = (float) $allocations->sum('available_hours');

        $hoursByCat = [];
        foreach ($allocations as $a) {
            foreach ($a->lines as $line) {
                $hoursByCat[$line->activity_category_id] = ($hoursByCat[$line->activity_category_id] ?? 0) + (float) $line->hours;
            }
        }

        $out = [];
        foreach ($hoursByCat as $catId => $hrs) {
            $out[$catId] = ['hours' => round($hrs, 2), 'pct' => $available > 0 ? round($hrs / $available * 100, 1) : 0.0];
        }

        return $out;
    }

    public function utilization(TeamMember $member, Carbon $month): float
    {
        $allocations = $this->monthAllocations($member, $month);
        $available = (float) $allocations->sum('available_hours');
        if ($available <= 0) {
            return 0.0;
        }

        $billableIds = $this->billableCategoryIds();
        $billableHours = 0.0;
        foreach ($allocations as $a) {
            foreach ($a->lines as $line) {
                if (in_array($line->activity_category_id, $billableIds, true)) {
                    $billableHours += (float) $line->hours;
                }
            }
        }

        return round($billableHours / $available * 100, 1);
    }

    public function utilizationTarget(): float
    {
        return (float) config('targets.default_utilization_pct', 70);
    }

    /** @return array{total: float, by_category: array<int, float>} */
    public function revenue(TeamMember $member, Carbon $month): array
    {
        $rows = $this->projects->recognizedRevenue($month)->where('team_member_id', $member->id);
        $byCat = [];
        foreach ($rows as $r) {
            $byCat[$r->activity_category_id] = ($byCat[$r->activity_category_id] ?? 0) + (float) $r->amount;
        }

        return ['total' => round((float) $rows->sum('amount'), 2), 'by_category' => $byCat];
    }

    /** @return array{count: int, pipeline_value: float} */
    public function output(TeamMember $member, Carbon $month): array
    {
        return $this->quotes->quotesProducedBy($member->user_id, $month);
    }

    /**
     * The intended allocation split per category (spec §12): delivery% spread
     * across the member's skill categories, plus pre_sales% and internal%.
     * @return array<int, float> keyed by activity_category_id
     */
    public function intendedSplit(TeamMember $member): array
    {
        $split = config('targets.default_split', ['delivery' => 70, 'pre_sales' => 20, 'internal' => 10]);
        $out = [];

        $skillIds = $member->categories()->pluck('activity_categories.id')->all();
        if (! empty($skillIds)) {
            $each = round($split['delivery'] / count($skillIds), 1);
            foreach ($skillIds as $id) {
                $out[$id] = $each;
            }
        }

        if ($pre = ActivityCategory::byKey('pre_sales')) {
            $out[$pre->id] = (float) ($split['pre_sales'] ?? 0);
        }
        if ($int = ActivityCategory::byKey('internal')) {
            $out[$int->id] = (float) ($split['internal'] ?? 0);
        }

        return $out;
    }

    /** RAG for utilization vs its target. */
    public function utilizationRag(float $actual): string
    {
        $target = $this->utilizationTarget();
        if ($actual >= $target) {
            return 'green';
        }

        return $actual >= $target * 0.8 ? 'amber' : 'red';
    }
}
