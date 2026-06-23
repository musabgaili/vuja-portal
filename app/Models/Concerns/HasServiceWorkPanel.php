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
        $this->mergeFillable(['worker_status']);
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
