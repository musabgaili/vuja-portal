<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceRequestStep extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'service_request_type_id',
        'name',
        'description',
        'step_order',
        'step_type',
        'step_config',
        'conditions',
        'actions',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        'step_config' => 'array',
        'conditions' => 'array',
        'actions' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'step_type', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the service request type that owns this step.
     */
    public function serviceRequestType(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestType::class);
    }

    /**
     * Get the form fields for this step.
     */
    public function formFields(): HasMany
    {
        return $this->hasMany(StepFormField::class)->orderBy('field_order');
    }

    /**
     * Get the active form fields for this step.
     */
    public function activeFormFields(): HasMany
    {
        return $this->hasMany(StepFormField::class)
            ->orderBy('field_order');
    }

    /**
     * Scope to get only active steps.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if this step is a form step.
     */
    public function isFormStep(): bool
    {
        return $this->step_type === 'form';
    }

    /**
     * Check if this step is an approval step.
     */
    public function isApprovalStep(): bool
    {
        return $this->step_type === 'approval';
    }

    /**
     * Check if this step is an assignment step.
     */
    public function isAssignmentStep(): bool
    {
        return $this->step_type === 'assignment';
    }

    /**
     * Check if this step requires external API.
     */
    public function isExternalApiStep(): bool
    {
        return $this->step_type === 'external_api';
    }

    /**
     * Get the step configuration value.
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->step_config, $key, $default);
    }

    /**
     * Get the conditions for this step.
     */
    public function getConditions(): array
    {
        return $this->conditions ?? [];
    }

    /**
     * Get the actions for this step.
     */
    public function getActions(): array
    {
        return $this->actions ?? [];
    }

    /**
     * Check if step should be shown based on conditions.
     */
    public function shouldShow(array $context = []): bool
    {
        $conditions = $this->getConditions();
        
        if (empty($conditions)) {
            return true;
        }

        // Simple condition checking - can be extended
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? 'equals';
            $value = $condition['value'] ?? null;
            $contextValue = data_get($context, $field);

            switch ($operator) {
                case 'equals':
                    if ($contextValue !== $value) return false;
                    break;
                case 'not_equals':
                    if ($contextValue === $value) return false;
                    break;
                case 'contains':
                    if (!str_contains($contextValue, $value)) return false;
                    break;
                case 'greater_than':
                    if ($contextValue <= $value) return false;
                    break;
                case 'less_than':
                    if ($contextValue >= $value) return false;
                    break;
            }
        }

        return true;
    }
}