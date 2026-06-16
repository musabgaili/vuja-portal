<?php

namespace App\Engagement\Adapters;

use App\Engagement\Contracts\BillingBridge;
use App\Models\Invoice;
use App\Models\Project;

/**
 * "Paid in full" is derived from the Invoice ledger: a project is paid in full
 * when it has at least one paid invoice and none are still awaiting payment.
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
        $invoices = Invoice::where('project_id', $projectId)->get(['status']);
        if ($invoices->isEmpty()) {
            return false;
        }
        $hasPaid = $invoices->contains(fn ($i) => $i->status === 'paid');
        $awaiting = $invoices->contains(fn ($i) => in_array($i->status, ['unpaid', 'proof_submitted'], true));

        return $hasPaid && ! $awaiting;
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
