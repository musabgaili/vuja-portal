<?php

namespace App\Services;

use App\Models\Project;
use Carbon\Carbon;

/**
 * Project health / risk-flagging engine — powers the Owner's Control Tower
 * (Green / Yellow / Red traffic light) and the predictive risk alerts:
 *   - no update in 3 days (stale)
 *   - a milestone within 24h of its deadline, or overdue
 *   - over budget, past end date, or a pending scope change
 */
class ProjectHealthService
{
    public const STALE_DAYS = 3;

    /** Statuses considered "active" (worth monitoring). */
    public const ACTIVE_STATUSES = ['planning', 'quoted', 'awarded', 'in_progress', 'paused'];

    /**
     * Compute risk flags for a project.
     * @return array{health:string, flags:array<int,array{key:string,severity:string,label:string}>}
     */
    public function evaluate(Project $project): array
    {
        $flags = [];
        $today = Carbon::today();

        // 1) Stale — no update in STALE_DAYS days.
        $lastComment = $project->comments()->max('created_at');
        $lastActivity = collect([$project->updated_at, $lastComment ? Carbon::parse($lastComment) : null])
            ->filter()->max();
        if ($lastActivity && $lastActivity->lt(Carbon::now()->subDays(self::STALE_DAYS))) {
            $flags[] = ['key' => 'stale', 'severity' => 'yellow',
                'label' => 'No update in '.$lastActivity->diffInDays(Carbon::now()).' days'];
        }

        // 2) Milestones overdue / due within 24h.
        foreach ($project->milestones()->where('status', '!=', 'completed')->whereNotNull('due_date')->get() as $m) {
            $due = Carbon::parse($m->due_date)->startOfDay();
            if ($due->lt($today)) {
                $flags[] = ['key' => 'milestone_overdue', 'severity' => 'red',
                    'label' => 'Milestone overdue: '.$m->title];
            } elseif ($due->lte($today->copy()->addDay())) {
                $flags[] = ['key' => 'milestone_due_soon', 'severity' => 'yellow',
                    'label' => 'Milestone due within 24h: '.$m->title];
            }
        }

        // 3) Over budget.
        if ($project->budget && (float) $project->spent > (float) $project->budget) {
            $flags[] = ['key' => 'over_budget', 'severity' => 'red',
                'label' => 'Over budget by '.number_format((float) $project->spent - (float) $project->budget)];
        }

        // 4) Past end date and not finished.
        if ($project->end_date && Carbon::parse($project->end_date)->lt($today)
            && ! in_array($project->status, ['completed', 'lost', 'cancelled'], true)) {
            $flags[] = ['key' => 'past_due', 'severity' => 'red', 'label' => 'Past its end date'];
        }

        // 5) Pending scope change awaiting decision.
        if (method_exists($project, 'hasPendingScopeChanges') && $project->hasPendingScopeChanges()) {
            $flags[] = ['key' => 'pending_scope', 'severity' => 'yellow', 'label' => 'Pending scope change'];
        }

        $health = 'green';
        foreach ($flags as $f) {
            if ($f['severity'] === 'red') { $health = 'red'; break; }
            $health = 'yellow';
        }

        return ['health' => $health, 'flags' => $flags];
    }

    /** Evaluate all active projects, returning rows sorted worst-first. */
    public function dashboard()
    {
        return Project::whereIn('status', self::ACTIVE_STATUSES)
            ->with(['client', 'projectManager', 'milestones'])
            ->get()
            ->map(fn (Project $p) => ['project' => $p] + $this->evaluate($p))
            ->sortBy(fn ($r) => ['red' => 0, 'yellow' => 1, 'green' => 2][$r['health']])
            ->values();
    }
}
