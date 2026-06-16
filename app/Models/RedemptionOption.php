<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RedemptionOption extends Model
{
    protected $fillable = [
        'key', 'name', 'name_ar', 'type', 'cost_points', 'value_meta', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'cost_points' => 'integer',
        'value_meta' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    public function meta(string $key, $default = null)
    {
        return data_get($this->value_meta, $key, $default);
    }
}
