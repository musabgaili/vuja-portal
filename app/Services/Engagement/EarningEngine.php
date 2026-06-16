<?php

namespace App\Services\Engagement;

use App\Engagement\Contracts\BillingBridge;
use App\Engagement\Contracts\IdeasBridge;
use App\Models\EarningRule;
use App\Models\PointsTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Fires earning awards from existing portal events (spec §5, §7). Each hook is
 * idempotent so a re-fired event never double-credits.
 */
class EarningEngine
{
    public function __construct(
        private PointsService $points,
        private IdeasBridge $ideas,
        private BillingBridge $billing,
        private ReferralService $referrals,
    ) {}

    /**
     * An innovation idea by a CLIENT was accepted. First N (cap_count, default 5)
     * auto-credit; beyond that the award is held for admin review (spec §7).
     */
    public function recordIdeaAccepted(User $client, Model $idea): ?PointsTransaction
    {
        if (! $client->isClient()) {
            return null; // internal staff use the separate Impact Points system
        }

        $rule = EarningRule::byKey('idea_accepted');
        if (! $rule || ! $rule->is_active) {
            return null;
        }

        // Idempotency: never award twice for the same idea.
        if ($this->alreadyAwarded('idea', $idea)) {
            return null;
        }

        $account = $this->points->accountFor($client);
        $cap = $rule->cap_count ?? 5;
        $acceptedSoFar = $this->ideas->acceptedIdeaCount($client->id); // includes the one just accepted
        $forceReview = $acceptedSoFar > $cap;

        return $this->points->awardByRule($account, 'idea_accepted', $idea, null, $forceReview);
    }

    /**
     * A project's invoices are now fully paid. Used to vest the REFERRAL payment
     * reward for whoever referred this client (spec §10). No self-earn rule for
     * paying in full unless an admin adds one.
     */
    public function recordPaidInFull(int $projectId): void
    {
        if (! $this->billing->isProjectPaidInFull($projectId)) {
            return;
        }
        $clientId = $this->billing->clientOf($projectId);
        if (! $clientId) {
            return;
        }
        $client = User::find($clientId);
        if (! $client) {
            return;
        }

        $this->referrals->rewardPaymentIfReferred($client, $projectId);
    }

    /** One-time award when a client has filled in their core profile (name, email, phone). */
    public function recordProfileComplete(User $client): ?PointsTransaction
    {
        if (! $client->isClient()) {
            return null;
        }
        $rule = EarningRule::byKey('profile_complete');
        if (! $rule || ! $rule->is_active) {
            return null;
        }
        if (blank($client->name) || blank($client->email) || blank($client->phone)) {
            return null;
        }
        $account = $this->points->accountFor($client);
        if ($account->transactions()->where('source', 'profile')->exists()) {
            return null;
        }

        return $this->points->awardByRule($account, 'profile_complete', $client);
    }

    private function alreadyAwarded(string $source, Model $reference): bool
    {
        return PointsTransaction::where('source', $source)
            ->where('reference_type', $reference->getMorphClass())
            ->where('reference_id', $reference->getKey())
            ->where('direction', 'earn')
            ->exists();
    }
}
