<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EarningRule extends Model
{
    protected $fillable = [
        'key', 'name', 'name_ar', 'points', 'is_active', 'auto_award',
        'requires_review', 'cap_count', 'cap_period', 'sort_order',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_active' => 'boolean',
        'auto_award' => 'boolean',
        'requires_review' => 'boolean',
        'cap_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public static function byKey(string $key): ?self
    {
        return static::where('key', $key)->first();
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }
}
