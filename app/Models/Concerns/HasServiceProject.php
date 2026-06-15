<?php

namespace App\Models\Concerns;

use App\Models\Project;

/**
 * Shared behaviour for service requests that can be converted into a delivery
 * Project (idea, consultation, research, IP, copyright, prototype). The Project
 * is found via its source_type/source_id back-reference to this service.
 */
trait HasServiceProject
{
    /** The Project this service was converted into, if any. */
    public function convertedProject(): ?Project
    {
        return Project::where('source_type', static::class)
            ->where('source_id', $this->getKey())
            ->first();
    }

    /** Whether this service has already been turned into a project. */
    public function isConvertedToProject(): bool
    {
        return $this->convertedProject() !== null;
    }
}
