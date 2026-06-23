<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslations;
use App\Models\Concerns\HasServiceProject;
use App\Models\Concerns\HasServiceWorkPanel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * A client request for the 3D lab — either 3D printing ("print this model") or
 * 3D design ("design a model for me"). Mirrors the other service requests:
 * submit + files -> manager assign/status -> convert to a project (quoted via the
 * Scope Planner using the existing 3D pricing rates).
 */
class ThreeDRequest extends Model
{
    use HasAutoTranslations;

    protected array $translatable = ['title', 'description'];

    use HasFactory, HasServiceProject, HasServiceWorkPanel, HasUuidRouteKey, LogsActivity;

    protected $fillable = [
        'uuid', 'user_id', 'type', 'title', 'description',
        'material', 'color', 'quantity', 'dimensions', 'finish',
        'output_format', 'complexity', 'reference_links',
        'budget_range', 'timeline', 'status', 'assigned_to', 'manager_notes',
    ];

    protected $casts = ['quantity' => 'integer'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'type', 'status', 'assigned_to'])
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
        return $this->hasMany(ThreeDRequestFile::class);
    }

    public function isPrinting(): bool { return $this->type === 'printing'; }
    public function isDesign(): bool { return $this->type === 'design'; }

    public function typeLabel(): string
    {
        return __('portal.threed.type.'.$this->type);
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
        return __('portal.threed.status.'.$this->status);
    }
}
