<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StepFormField extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'service_request_step_id',
        'field_name',
        'field_label',
        'field_type',
        'field_description',
        'field_options',
        'validation_rules',
        'is_required',
        'field_order',
        'field_config',
    ];

    protected $casts = [
        'field_options' => 'array',
        'validation_rules' => 'array',
        'field_config' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['field_name', 'field_label', 'field_type', 'is_required'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the service request step that owns this field.
     */
    public function serviceRequestStep(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestStep::class);
    }

    /**
     * Get the field options as an array.
     */
    public function getFieldOptions(): array
    {
        return $this->field_options ?? [];
    }

    /**
     * Get the validation rules as an array.
     */
    public function getValidationRules(): array
    {
        $rules = $this->validation_rules ?? [];
        
        if ($this->is_required) {
            $rules[] = 'required';
        }

        return $rules;
    }

    /**
     * Get the field configuration value.
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->field_config, $key, $default);
    }

    /**
     * Check if this field is a text input.
     */
    public function isTextInput(): bool
    {
        return in_array($this->field_type, ['text', 'email', 'number', 'tel', 'url']);
    }

    /**
     * Check if this field is a textarea.
     */
    public function isTextarea(): bool
    {
        return $this->field_type === 'textarea';
    }

    /**
     * Check if this field is a select dropdown.
     */
    public function isSelect(): bool
    {
        return in_array($this->field_type, ['select', 'radio', 'checkbox']);
    }

    /**
     * Check if this field is a file upload.
     */
    public function isFileUpload(): bool
    {
        return $this->field_type === 'file';
    }

    /**
     * Check if this field is a date picker.
     */
    public function isDatePicker(): bool
    {
        return in_array($this->field_type, ['date', 'datetime-local']);
    }

    /**
     * Get the HTML input type.
     */
    public function getHtmlInputType(): string
    {
        return match($this->field_type) {
            'email' => 'email',
            'number' => 'number',
            'tel' => 'tel',
            'url' => 'url',
            'date' => 'date',
            'datetime-local' => 'datetime-local',
            'file' => 'file',
            default => 'text'
        };
    }

    /**
     * Get the field placeholder text.
     */
    public function getPlaceholder(): string
    {
        return $this->getConfig('placeholder', "Enter {$this->field_label}");
    }

    /**
     * Get the field help text.
     */
    public function getHelpText(): ?string
    {
        return $this->field_description ?: $this->getConfig('help_text');
    }

    /**
     * Get the field validation message.
     */
    public function getValidationMessage(): string
    {
        return $this->getConfig('validation_message', "The {$this->field_label} field is required.");
    }
}