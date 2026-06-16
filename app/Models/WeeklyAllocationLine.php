<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyAllocationLine extends Model
{
    protected $fillable = [
        'weekly_allocation_id', 'activity_category_id', 'percent', 'hours',
    ];

    protected $casts = [
        'percent' => 'decimal:2',
        'hours' => 'decimal:2',
    ];

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(WeeklyAllocation::class, 'weekly_allocation_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ActivityCategory::class, 'activity_category_id');
    }
}
