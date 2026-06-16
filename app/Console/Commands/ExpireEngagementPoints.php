<?php

namespace App\Console\Commands;

use App\Services\Engagement\PointsService;
use Illuminate\Console\Command;

class ExpireEngagementPoints extends Command
{
    protected $signature = 'engagement:expire-points';

    protected $description = 'Expire client engagement points past the 24-month window (FIFO).';

    public function handle(PointsService $points): int
    {
        $expired = $points->expire();
        $this->info("Expired {$expired} engagement point(s).");

        return self::SUCCESS;
    }
}
