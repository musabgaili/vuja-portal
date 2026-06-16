<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityCategory extends Model
{
    protected $fillable = [
        'key', 'name', 'name_ar', 'kind', 'is_billable', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_billable' => 'boolean',
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

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    public function isDelivery(): bool
    {
        return $this->kind === 'delivery';
    }
}
