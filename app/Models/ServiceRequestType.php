<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceRequestType extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user who created this service request type.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the steps for this service request type.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(ServiceRequestStep::class)->orderBy('step_order');
    }

    /**
     * Get the active steps for this service request type.
     */
    public function activeSteps(): HasMany
    {
        return $this->hasMany(ServiceRequestStep::class)
            ->where('is_active', true)
            ->orderBy('step_order');
    }

    /**
     * Get the service requests of this type.
     */
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'service_request_type_id');
    }

    /**
     * Scope to get only active service request types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Get the icon HTML.
     */
    public function getIconHtml(): string
    {
        return "<i class=\"{$this->icon}\"></i>";
    }

    /**
     * Get the color CSS variable.
     */
    public function getColorCss(): string
    {
        return "var(--service-{$this->slug}-color, {$this->color})";
    }
}