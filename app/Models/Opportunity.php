<?php

namespace App\Models;

use App\Models\Concerns\HasCrmActivities;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use HasCrmActivities, HasTags;

    protected $fillable = [
        'name', 'company_name', 'contact_name', 'email', 'phone', 'source',
        'stage', 'expected_value', 'probability', 'expected_close_date',
        'owner_id', 'client_id', 'company_id', 'contact_id', 'description', 'lost_reason',
        'won_at', 'lost_at', 'converted_project_id',
    ];

    protected $casts = [
        'expected_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'converted_project_id');
    }

    public function isOpen(): bool
    {
        return ! in_array($this->stage, ['won', 'lost'], true);
    }

    public function isWon(): bool
    {
        return $this->stage === 'won';
    }

    public function isLost(): bool
    {
        return $this->stage === 'lost';
    }

    /** Weighted forecast value (expected value * probability). */
    public function weightedValue(): float
    {
        return round((float) $this->expected_value * (int) $this->probability / 100, 2);
    }

    public function stageColor(): string
    {
        return match ($this->stage) {
            'won' => 'success',
            'lost' => 'danger',
            default => PipelineStage::colorMap()[$this->stage] ?? 'secondary',
        };
    }

    /** Human label for the current stage (DB stage label, or Won/Lost). */
    public function stageLabel(): string
    {
        return PipelineStage::map()[$this->stage]
            ?? match ($this->stage) { 'won' => 'Won', 'lost' => 'Lost', default => ucfirst((string) $this->stage) };
    }
}
