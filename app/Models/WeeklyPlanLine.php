<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One timesheet line of a weekly plan: hours-per-day logged against an assigned
 * project, a direct staff task, or a fixed activity.
 */
class WeeklyPlanLine extends Model
{
    protected $fillable = [
        'weekly_plan_id', 'kind', 'project_id', 'staff_task_id', 'activity', 'label', 'hours',
    ];

    protected $casts = [
        'hours' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WeeklyPlan::class, 'weekly_plan_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function staffTask(): BelongsTo
    {
        return $this->belongsTo(StaffTask::class);
    }

    /** Sum of hours across the week for this line. */
    public function totalHours(): int
    {
        return (int) array_sum($this->hours ?? []);
    }

    /**
     * The reporting category this line rolls up into: project/task lines count as
     * "Projects"; activity lines use their own activity label.
     */
    public function category(): string
    {
        if ($this->kind === 'activity') {
            return config("planner.activities.$this->activity", $this->label);
        }

        return 'Projects & Tasks';
    }
}
