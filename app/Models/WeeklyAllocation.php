<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyAllocation extends Model
{
    protected $fillable = [
        'team_member_id', 'week_start', 'available_hours', 'status',
    ];

    protected $casts = [
        'week_start' => 'date',
        'available_hours' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(WeeklyAllocationLine::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
