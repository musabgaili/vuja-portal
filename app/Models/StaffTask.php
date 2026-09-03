<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTask extends Model
{
    /** @use HasFactory<\Database\Factories\StaffTaskFactory> */
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'category', 'priority', 'status',
        'assigned_to', 'assigned_by', 'project_id', 'opportunity_id',
        'due_date', 'completed_at', 'points_awarded',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'points_awarded' => 'boolean',
    ];

    public const CATEGORIES = ['project', 'sales', 'presale', 'management'];

    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public const STATUSES = ['open', 'in_progress', 'done', 'cancelled'];

    /** Engagement action key (and IP rate) for each category. */
    public function engagementAction(): string
    {
        return 'staff_task_'.$this->category;
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && ! $this->isDone()
            && $this->status !== 'cancelled'
            && $this->due_date->isPast();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
