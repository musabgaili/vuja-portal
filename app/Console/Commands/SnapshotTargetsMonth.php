<?php

namespace App\Console\Commands;

use App\Services\Targets\TargetService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Safety-net monthly snapshot: on the 1st, record the just-ended month into
 * target_logs so a missed manual "Record month" never loses the trend. Idempotent
 * (updateOrCreate), so it's harmless if the manager already recorded it.
 */
class SnapshotTargetsMonth extends Command
{
    protected $signature = 'targets:snapshot-month {--month= : YYYY-MM to snapshot (defaults to last month)}';

    protected $description = 'Snapshot a month of performance targets into the trend log.';

    public function handle(TargetService $targets): int
    {
        $period = $this->option('month')
            ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
            : now()->subMonthNoOverflow()->startOfMonth();

        $n = $targets->recordMonth($period);
        $this->info("Recorded {$n} target snapshot(s) for {$period->format('Y-m')}.");

        return self::SUCCESS;
    }
}
