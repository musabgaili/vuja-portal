<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\User;
use App\Models\WeeklyPlan;

/**
 * Aggregates everything awaiting a manager's / project-manager's decision —
 * the "Approval Queue". Currently: quotes pending internal approval and
 * weekly timesheets pending review.
 */
class ApprovalService
{
    /** Quotes submitted for internal approval (manager or PM can act). */
    public function pendingQuotes()
    {
        return Quote::where('status', 'pending_approval')
            ->with(['creator', 'opportunity', 'client'])
            ->latest('updated_at')->get();
    }

    /** Timesheets awaiting manager review — ALL pending weeks (so late/back-dated
     *  submissions for the current or a past week also surface, newest week first). */
    public function pendingPlans()
    {
        return WeeklyPlan::where('status', 'pending')
            ->with('user')
            ->orderByDesc('week_start')
            ->latest('submitted_at')
            ->get();
    }

    /** Total count relevant to this approver (quotes for all approvers; plans for managers). */
    public function count(User $user): int
    {
        $n = $this->pendingQuotes()->count();
        if ($user->isManager()) {
            $n += $this->pendingPlans()->count();
        }

        return $n;
    }
}
