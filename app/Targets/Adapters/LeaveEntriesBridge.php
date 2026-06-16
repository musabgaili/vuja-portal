<?php

namespace App\Targets\Adapters;

use App\Models\LeaveEntry;
use App\Targets\Contracts\LeaveBridge;
use Carbon\Carbon;

class LeaveEntriesBridge implements LeaveBridge
{
    public function leaveHours(int $teamMemberId, Carbon $weekStart): float
    {
        $start = $weekStart->copy()->startOfDay();
        $end = $weekStart->copy()->addDays(6)->endOfDay();

        return (float) LeaveEntry::where('team_member_id', $teamMemberId)
            ->whereBetween('date', [$start, $end])->sum('hours');
    }
}
