<?php

namespace App\Targets\Adapters;

use App\Models\Quote;
use App\Targets\Contracts\QuotesBridge;
use Carbon\Carbon;

class ScopeQuotesBridge implements QuotesBridge
{
    public function quotesProducedBy(int $userId, Carbon $monthStart): array
    {
        $start = $monthStart->copy()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $quotes = Quote::where('created_by', $userId)
            ->whereNotNull('sent_at')->whereBetween('sent_at', [$start, $end])->get();

        return [
            'count' => $quotes->count(),
            'pipeline_value' => (float) $quotes->sum(fn (Quote $q) => $q->invoiceTotal()),
        ];
    }
}
