<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    protected $fillable = [
        'key', 'name', 'name_ar', 'min_lifetime_points', 'perks', 'badge', 'sort_order',
    ];

    protected $casts = [
        'perks' => 'array',
        'min_lifetime_points' => 'integer',
        'sort_order' => 'integer',
    ];

    public function localizedName(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    /**
     * Perks for the current locale. Supports the bilingual shape
     * ['en' => [...], 'ar' => [...]] and falls back to a legacy flat array.
     */
    public function localizedPerks(): array
    {
        $perks = $this->perks;
        if (is_array($perks) && (isset($perks['en']) || isset($perks['ar']))) {
            $set = app()->getLocale() === 'ar'
                ? ($perks['ar'] ?? $perks['en'] ?? [])
                : ($perks['en'] ?? []);

            return (array) $set;
        }

        return (array) ($perks ?? []);
    }
}
