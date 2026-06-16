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

class IdeaRequest extends Model
{
    use HasAutoTranslations;

    protected array $translatable = ['title', 'description'];

    use HasFactory, HasServiceProject, HasUuidRouteKey, LogsActivity;

    protected $fillable = [
        'user_id',
        'client_type',
        'idea_status',
        'title',
        'description',
        'target_market',
        'problem_solving',
        'unique_value',
        'status',
        'ai_assessment_data',
        'tokens_used',
        'negotiation_notes',
        'initial_quote',
        'final_quote',
        'quote_file_path',
        'quote_status',
        'quote_approved_by',
        'quote_approved_at',
        'agreement_terms',
        'agreement_accepted_at',
        'payment_file',
        'payment_verified_at',
        'assigned_to',
        'manager_id',
    ];

    protected $casts = [
        'ai_assessment_data' => 'array',
        'agreement_accepted_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'quote_approved_at' => 'datetime',
        'initial_quote' => 'decimal:2',
        'final_quote' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'final_quote'])
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

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IdeaRequestComment::class);
    }

    // Status helper methods
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isInNegotiation(): bool
    {
        return $this->status === 'negotiation';
    }

    public function isQuoted(): bool
    {
        return $this->status === 'quoted';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isPaymentPending(): bool
    {
        return $this->status === 'payment_pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getStatusBadgeColor(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'submitted' => 'info',
            'ai_assessment' => 'primary',
            'negotiation' => 'warning',
            'quoted' => 'info',
            'accepted' => 'success',
            'rejected' => 'error',
            'payment_pending' => 'warning',
            'approved' => 'success',
            'in_progress' => 'primary',
            'completed' => 'success',
            default => 'secondary'
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => __('portal.ideas.status.draft'),
            'submitted' => __('portal.ideas.status.submitted'),
            'ai_assessment' => __('portal.ideas.status.ai_assessment'),
            'negotiation' => __('portal.ideas.status.negotiation'),
            'quoted' => __('portal.ideas.status.quoted'),
            'accepted' => __('portal.ideas.status.accepted'),
            'rejected' => __('portal.ideas.status.rejected'),
            'payment_pending' => __('portal.ideas.status.payment_pending'),
            'approved' => __('portal.ideas.status.approved'),
            'in_progress' => __('portal.ideas.status.in_progress'),
            'completed' => __('portal.ideas.status.completed'),
            default => str_replace('_', ' ', $this->status)
        };
    }
}
