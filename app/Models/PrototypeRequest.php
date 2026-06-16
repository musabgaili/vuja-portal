<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasServiceProject;

class PrototypeRequest extends Model
{
    use HasAutoTranslations;

    protected array $translatable = ['title', 'description'];

    use HasFactory, HasServiceProject, HasUuidRouteKey, LogsActivity;

    protected $fillable = [
        'uuid', 'user_id', 'title', 'category', 'description', 'goals',
        'budget_range', 'timeline', 'status', 'assigned_to', 'manager_notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'assigned_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function files(): HasMany
    {
        return $this->hasMany(PrototypeRequestFile::class);
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'submitted' => 'info',
            'assigned' => 'primary',
            'in_progress' => 'warning',
            'completed' => 'success',
            'rejected', 'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return __('portal.prototypes.status.'.$this->status);
    }
}
