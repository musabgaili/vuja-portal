<?php

namespace App\Targets\Adapters;

use App\Models\ProjectAssignment;
use App\Models\Quote;
use App\Targets\Contracts\ProjectsBridge;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Revenue is recognized in the month a project is COMPLETED (delivery), summing
 * the assigned service-line values per engineer + category. The Electronic
 * Components line is never assignable (materials, not engineer revenue).
 */
class ProjectAssignmentsBridge implements ProjectsBridge
{
    /** Per-request memo keyed by month (the manager dashboard calls this once per engineer). */
    private array $cache = [];

    public function recognizedRevenue(Carbon $monthStart): Collection
    {
        $start = $monthStart->copy()->startOfMonth();
        $key = $start->format('Y-m');
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $end = $start->copy()->endOfMonth();

        return $this->cache[$key] = ProjectAssignment::with('category')
            ->whereHas('project', fn ($q) => $q->where('status', 'completed')->whereBetween('actual_end_date', [$start, $end]))
            ->get()
            ->map(fn (ProjectAssignment $a) => (object) [
                'team_member_id' => $a->team_member_id,
                'activity_category_id' => $a->activity_category_id,
                'category_key' => $a->category?->key,
                'amount' => (float) $a->value,
                'project_id' => $a->project_id,
            ]);
    }

    public function assignableLines(int $projectId): Collection
    {
        $quote = Quote::where('project_id', $projectId)->first();
        if (! $quote) {
            return collect();
        }

        return $quote->items()->whereIn('type', ['service', 'other'])->orderBy('sort_order')->get()
            ->map(fn ($it) => (object) [
                'line_id' => $it->id,
                'name' => $it->name,
                'category' => $it->category,
                'amount' => (float) $it->line_client,
            ]);
    }
}
