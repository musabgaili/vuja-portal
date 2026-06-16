<?php

namespace App\Engagement\Adapters;

use App\Engagement\Contracts\BillingBridge;
use App\Models\Invoice;
use App\Models\Project;

/**
 * "Paid in full" is derived from the Invoice ledger: a project is paid in full
 * when nothing is still awaiting payment AND the paid amount covers the project's
 * value — so one small token invoice can't vest a referral reward on a big deal.
 */
class InvoiceBillingAdapter implements BillingBridge
{
    public function projectValue(int $projectId): float
    {
        $budget = (float) (Project::where('id', $projectId)->value('budget') ?? 0);
        if ($budget > 0) {
            return $budget;
        }

        return (float) Invoice::where('project_id', $projectId)->where('status', 'paid')->sum('amount');
    }

    public function isProjectPaidInFull(int $projectId): bool
    {
        $invoices = Invoice::where('project_id', $projectId)->get(['status', 'amount']);
        if ($invoices->isEmpty()) {
            return false;
        }
        $hasPaid = $invoices->contains(fn ($i) => $i->status === 'paid');
        $awaiting = $invoices->contains(fn ($i) => in_array($i->status, ['unpaid', 'proof_submitted'], true));
        if (! $hasPaid || $awaiting) {
            return false;
        }

        // Guard against a token invoice: the paid total must cover the project's
        // expected value (budget). If no budget is set, the no-outstanding rule stands.
        $budget = (float) (Project::where('id', $projectId)->value('budget') ?? 0);
        if ($budget > 0) {
            $paid = (float) $invoices->where('status', 'paid')->sum('amount');

            return $paid + 0.01 >= $budget; // tiny epsilon for decimal rounding
        }

        return true;
    }

    public function clientOf(int $projectId): ?int
    {
        $id = Project::where('id', $projectId)->value('client_id');

        return $id ? (int) $id : null;
    }

    public function firstPaidProjectForClient(int $clientId): ?int
    {
        $projectIds = Project::where('client_id', $clientId)->orderBy('id')->pluck('id');
        foreach ($projectIds as $id) {
            if ($this->isProjectPaidInFull((int) $id)) {
                return (int) $id;
            }
        }

        return null;
    }
}
