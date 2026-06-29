<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyPlan extends Model
{
    protected $fillable = [
        'user_id', 'week_start', 'status',
        'hours_projects', 'hours_development', 'hours_presale',
        'locations', 'availability', 'submitted_at', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'week_start' => 'date',
        'locations' => 'array',
        'availability' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'hours_projects' => 'integer',
        'hours_development' => 'integer',
        'hours_presale' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(WeeklyPlanLine::class);
    }

    /** Total logged hours for the week, summed across every timesheet line. */
    public function totalHours(): int
    {
        return (int) $this->lines->sum(fn (WeeklyPlanLine $l) => $l->totalHours());
    }

    /** Hours grouped by reporting category, e.g. ['Projects & Tasks' => 24, 'Vacation' => 8]. */
    public function categoryBreakdown(): array
    {
        return $this->lines
            ->groupBy(fn (WeeklyPlanLine $l) => $l->category())
            ->map(fn ($group) => (int) $group->sum(fn (WeeklyPlanLine $l) => $l->totalHours()))
            ->filter(fn ($h) => $h > 0)
            ->toArray();
    }

    public function isComplete(): bool
    {
        $required = $this->user?->plannerRequiredHours() ?? (int) config('planner.required_hours', 40);

        return $this->totalHours() === $required;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'overdue' => 'danger',
            default => 'secondary',
        };
    }

    /** Submission deadline for this plan's week: Saturday (the day before) at the configured time. */
    public function deadline(): \Carbon\Carbon
    {
        [$h, $m] = explode(':', (string) config('planner.deadline_time', '18:00'));

        return \Carbon\Carbon::parse($this->week_start)->subDay()->setTime((int) $h, (int) $m);
    }

    /** Whether the timesheet was submitted after its deadline (or its week already started). */
    public function isLate(): bool
    {
        return $this->submitted_at !== null && $this->submitted_at->greaterThan($this->deadline());
    }
}
