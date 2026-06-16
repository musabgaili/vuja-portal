<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetLog extends Model
{
    protected $fillable = [
        'user_id', 'period', 'target_metric_id', 'target_value', 'actual_value',
        'attainment_pct', 'recorded_by', 'recorded_at',
    ];

    protected $casts = [
        'period' => 'date',
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'attainment_pct' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(TargetMetric::class, 'target_metric_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
