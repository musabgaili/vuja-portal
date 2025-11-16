<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ProjectExpense extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'project_id', 'logged_by', 'title', 'description',
        'amount', 'category', 'expense_date', 'receipt_file',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'amount', 'category', 'expense_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Expense logged',
                'updated' => 'Expense updated',
                'deleted' => 'Expense deleted',
                default => $eventName
            });
    }
    
    public function tapActivity($activity, string $eventName)
    {
        $activity->subject_id = $this->project_id;
        $activity->subject_type = Project::class;
    }
}
