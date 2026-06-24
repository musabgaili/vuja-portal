<?php

namespace App\Models\Concerns;

use App\Models\ServiceWorkItem;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Shared "work panel" for every assignable service request (prototype, 3D, IP,
 * copyright, research, consultation, idea). Gives the assigned employee / PM a
 * uniform place to:
 *   - track their own progress (worker_status), independent of the service's
 *     business-stage `status` field which managers drive;
 *   - leave internal-only work notes;
 *   - attach deliverables (their finished output), optionally shared to the client.
 */
trait HasServiceWorkPanel
{
    /** Eloquent calls this automatically when the trait is used (boot-time). */
    public function initializeHasServiceWorkPanel(): void
    {
        $this->mergeFillable(['worker_status', 'service_completed_at']);
        $this->mergeCasts(['service_completed_at' => 'datetime']);
    }

    /**
     * Eloquent calls this automatically (boot-time). The first time a service
     * request transitions into its terminal "completed" state we stamp
     * `service_completed_at` and award the assignee target-gated impact points
     * (only beyond their monthly "services completed" target — see TargetPointsGate).
     */
    /**
     * Statuses that count as "this service request is delivered". Override per
     * model — e.g. IP/copyright registrations finish at 'registered'.
     */
    public function serviceCompletionStatuses(): array
    {
        return ['completed'];
    }

    public static function bootHasServiceWorkPanel(): void
    {
        static::updated(function ($model) {
            if (! $model->wasChanged('status')) {
                return;
            }
            $terminal = $model->serviceCompletionStatuses();
            // Fire once: now terminal, but wasn't terminal before this change.
            if (! in_array($model->status, $terminal, true) || in_array($model->getOriginal('status'), $terminal, true)) {
                return;
            }
            if (! $model->assigned_to) {
                return;
            }

            // Stamp the completion time once (quietly — don't re-fire this hook).
            if (! $model->service_completed_at) {
                $model->service_completed_at = now();
                $model->saveQuietly();
            }

            $assignee = \App\Models\User::find($model->assigned_to);
            if (! $assignee) {
                return;
            }

            // Idempotency: never award twice for the same service request.
            $already = \App\Models\EngagementLog::where('subject_type', $model->getMorphClass())
                ->where('subject_id', $model->getKey())
                ->where('action', 'service_completed')
                ->exists();
            if ($already) {
                return;
            }

            app(\App\Services\Targets\TargetPointsGate::class)
                ->awardIfEarned($assignee, 'service_completed', $model, 'Service completed: '.($model->title ?? ('#'.$model->getKey())));
        });
    }

    public function workItems(): MorphMany
    {
        return $this->morphMany(ServiceWorkItem::class, 'noteable')->latest();
    }

    public function workNotes(): MorphMany
    {
        return $this->morphMany(ServiceWorkItem::class, 'noteable')
            ->where('type', 'note')->latest();
    }

    public function deliverables(): MorphMany
    {
        return $this->morphMany(ServiceWorkItem::class, 'noteable')
            ->where('type', 'deliverable')->latest();
    }

    public function clientDeliverables(): MorphMany
    {
        return $this->deliverables()->where('is_client_visible', true);
    }

    /** Default to "not_started" when the column is null/unset. */
    public function workerStatusValue(): string
    {
        return $this->worker_status ?: 'not_started';
    }

    public function workerStatusLabel(): string
    {
        return __('portal.service_work.worker_status.'.$this->workerStatusValue());
    }

    public function workerStatusBadge(): string
    {
        return match ($this->workerStatusValue()) {
            'in_progress' => 'warning',
            'submitted_for_review' => 'success',
            default => 'secondary',
        };
    }
}
