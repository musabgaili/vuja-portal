<?php

namespace App\Models;

use App\Models\Concerns\HasAutoTranslations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Concerns\HasServiceProject;
use App\Models\Concerns\HasServiceWorkPanel;

class CopyrightRegistration extends Model
{
    use HasAutoTranslations;

    protected array $translatable = ['title', 'work_description'];

    use HasFactory, HasServiceProject, HasServiceWorkPanel, HasUuidRouteKey, LogsActivity;

    protected $fillable = [
        'user_id',
        'title',
        'work_description',
        'work_type',
        'work_files',
        'status',
        'meeting_requested_at',
        'meeting_confirmed_at',
        'meeting_link',
        'copyright_number',
        'filed_at',
        'registered_at',
        'assigned_to',
    ];

    protected $casts = [
        'work_files' => 'array',
        'meeting_requested_at' => 'datetime',
        'meeting_confirmed_at' => 'datetime',
        'filed_at' => 'datetime',
        'registered_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'copyright_number'])
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

    // Status helper methods
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isMeetingBooked(): bool
    {
        return $this->status === 'meeting_booked';
    }

    public function isMeetingConfirmed(): bool
    {
        return $this->status === 'meeting_confirmed';
    }

    public function isFiling(): bool
    {
        return $this->status === 'filing';
    }

    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /** A copyright registration is delivered once it is registered (or later completed). */
    public function serviceCompletionStatuses(): array
    {
        return ['registered', 'completed'];
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'submitted' => 'info',
            'meeting_booked' => 'warning',
            'meeting_confirmed' => 'success',
            'filing' => 'warning',
            'registered' => 'success',
            'completed' => 'success',
            'cancelled' => 'error',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'submitted' => __('portal.copyright.status_submitted'),
            'meeting_booked' => __('portal.copyright.status_meeting_booked'),
            'meeting_confirmed' => __('portal.copyright.status_meeting_confirmed'),
            'filing' => __('portal.copyright.status_filing'),
            'registered' => __('portal.copyright.status_registered'),
            'completed' => __('portal.copyright.status_completed'),
            'cancelled' => __('portal.copyright.status_cancelled'),
            default => str_replace('_', ' ', $this->status)
        };
    }

    /** Locale-aware work-type label (maps the stored English value to a key, falls back to raw). */
    public function workTypeLabel(): string
    {
        $key = 'portal.copyright.types.'.\Illuminate\Support\Str::slug((string) $this->work_type, '_');

        return \Illuminate\Support\Facades\Lang::has($key) ? __($key) : (string) $this->work_type;
    }
}
