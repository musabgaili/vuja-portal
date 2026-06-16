<?php

namespace App\Targets\Contracts;

use Carbon\Carbon;

/** Binds to HR/leave (here: the leave_entries table). Spec §5. */
interface LeaveBridge
{
    /** Leave hours for a team member in the ISO week starting $weekStart. */
    public function leaveHours(int $teamMemberId, Carbon $weekStart): float;
}
