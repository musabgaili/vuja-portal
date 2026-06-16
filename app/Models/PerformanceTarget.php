<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceTarget extends Model
{
    protected $fillable = [
        'user_id', 'period', 'target_metric_id', 'target_value', 'current_value', 'notes', 'created_by',
    ];

    protected $casts = [
        'period' => 'date',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function metric(): BelongsTo
    {
        return $this->belongsTo(TargetMetric::class, 'target_metric_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
