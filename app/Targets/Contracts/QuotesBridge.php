<?php

namespace App\Targets\Contracts;

use Carbon\Carbon;

/** Binds to the Scope Planner quotes — pre-sales output. Spec §5. */
interface QuotesBridge
{
    /**
     * Quotes a user produced in the month + their pipeline value.
     * @return array{count: int, pipeline_value: float}
     */
    public function quotesProducedBy(int $userId, Carbon $monthStart): array;
}
