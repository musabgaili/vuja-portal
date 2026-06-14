<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyPlan extends Model
{
    protected $fillable = [
        'user_id', 'week_start', 'status',
        'hours_projects', 'hours_development', 'hours_presale',
        'locations', 'submitted_at', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'week_start' => 'date',
        'locations' => 'array',
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

    public function totalHours(): int
    {
        return (int) $this->hours_projects + (int) $this->hours_development + (int) $this->hours_presale;
    }

    public function isComplete(): bool
    {
        return $this->totalHours() === (int) config('planner.required_hours', 40);
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
}
