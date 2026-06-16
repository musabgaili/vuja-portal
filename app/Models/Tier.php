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
}
