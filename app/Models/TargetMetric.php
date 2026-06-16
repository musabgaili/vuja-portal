<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TargetMetric extends Model
{
    protected $fillable = [
        'key', 'name', 'name_ar', 'source', 'unit', 'engagement_action', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public static function byKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    /** The metric (if any) that governs a given impact-points action — used by the gate. */
    public static function byEngagementAction(string $action): ?self
    {
        return static::where('engagement_action', $action)->where('is_active', true)->first();
    }

    public function isAuto(): bool
    {
        return $this->source === 'auto';
    }

    public function isCurrency(): bool
    {
        return $this->unit === 'sar';
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }
}
