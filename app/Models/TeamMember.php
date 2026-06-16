<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamMember extends Model
{
    protected $fillable = [
        'user_id', 'display_name', 'specialization', 'weekly_capacity_hours', 'is_active',
    ];

    protected $casts = [
        'weekly_capacity_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(MemberSkill::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ActivityCategory::class, 'member_skills');
    }

    public function weeklyAllocations(): HasMany
    {
        return $this->hasMany(WeeklyAllocation::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class);
    }

    public function leaveEntries(): HasMany
    {
        return $this->hasMany(LeaveEntry::class);
    }
}
