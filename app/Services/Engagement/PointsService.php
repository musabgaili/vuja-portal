<?php

namespace App\Services\Engagement;

use App\Models\EarningRule;
use App\Models\PointsAccount;
use App\Models\PointsTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The append-only points ledger (spec §8). Never mutate a balance directly:
 * every change is a points_transactions row, and balance / lifetime / tier on
 * points_accounts are caches recomputed from the ledger on every write.
 *
 *   balance  = Σ remaining over APPROVED earns        (spend & expiry decrement remaining)
 *   lifetime = Σ points    over APPROVED earns        (sticky: spend & expiry never reduce it;
 *                                                       only a reversal/clawback excludes an earn)
 */
class PointsService
{
    public function __construct(private TierService $tiers) {}

    public function accountFor(User $client): PointsAccount
    {
        return $client->ensurePointsAccount();
    }

    /**
     * Award points for an earning rule. Returns the transaction, or null when
     * the rule is inactive / has no points. $forceReview routes the earn to a
     * pending review (e.g. the 6th+ accepted idea).
     */
    public function awardByRule(
        PointsAccount $account,
        string $ruleKey,
        ?Model $reference = null,
        ?string $description = null,
        bool $forceReview = false,
        ?int $createdBy = null,
        bool $ignoreRuleReview = false,
    ): ?PointsTransaction {
        $rule = EarningRule::byKey($ruleKey);
        if (! $rule || ! $rule->is_active || $rule->points <= 0) {
            return null;
        }

        // $ignoreRuleReview is set when the review already happened upstream
        // (a claim being approved, a grant being approved) — credit immediately.
        $requiresReview = ($ignoreRuleReview ? false : $rule->requires_review) || $forceReview;

        return $this->awardPoints(
            account: $account,
            points: (int) $rule->points,
            source: $this->sourceForRule($ruleKey),
            reference: $reference,
            description: $description ?? $rule->name,
            requiresReview: $requiresReview,
            createdBy: $createdBy,
        );
    }

    /** Raw earn (manual grants, welcome perks, claim approvals). No cap logic — callers enforce caps. */
    public function awardPoints(
        PointsAccount $account,
        int $points,
        string $source,
        ?Model $reference = null,
        ?string $description = null,
        bool $requiresReview = false,
        ?int $createdBy = null,
    ): PointsTransaction {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Award points must be positive.');
        }

        return DB::transaction(function () use ($account, $points, $source, $reference, $description, $requiresReview, $createdBy) {
            $expiryMonths = (int) config('engagement_points.expiry_months', 24);
            $approved = ! $requiresReview;

            $txn = new PointsTransaction([
                'direction' => 'earn',
                'source' => $source,
                'points' => $points,
                'remaining' => $approved ? $points : null,
                'description' => $description,
                'status' => $approved ? 'approved' : 'pending',
                'earned_at' => now(),
                'expires_at' => now()->addMonths($expiryMonths),
                'created_by' => $createdBy,
            ]);
            $txn->points_account_id = $account->id;
            if ($reference) {
                $txn->reference()->associate($reference);
            }
            $txn->save();

            $this->recompute($account);

            return $txn;
        });
    }

    /** Approve a pending earn (idea>5 or a verified claim) → credits points. */
    public function approveEarn(PointsTransaction $txn, ?int $approverId = null): PointsTransaction
    {
        if ($txn->direction !== 'earn' || $txn->status !== 'pending') {
            return $txn;
        }

        return DB::transaction(function () use ($txn, $approverId) {
            $txn->forceFill([
                'status' => 'approved',
                'remaining' => $txn->points,
                'approved_by' => $approverId,
            ])->save();

            $this->recompute($txn->account);

            return $txn;
        });
    }

    /** Reject a pending earn — never credited. */
    public function rejectEarn(PointsTransaction $txn, ?int $approverId = null): PointsTransaction
    {
        if ($txn->direction !== 'earn' || $txn->status !== 'pending') {
            return $txn;
        }
        $txn->forceFill(['status' => 'rejected', 'remaining' => 0, 'approved_by' => $approverId])->save();

        return $txn;
    }

    /** Spend points (FIFO over live earns). Throws if the balance is insufficient. */
    public function spend(
        PointsAccount $account,
        int $points,
        string $source,
        ?Model $reference = null,
        ?string $description = null,
    ): PointsTransaction {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Spend points must be positive.');
        }

        return DB::transaction(function () use ($account, $points, $source, $reference, $description) {
            $account = PointsAccount::lockForUpdate()->findOrFail($account->id);

            $spendable = (int) $account->transactions()->liveEarns()->sum('remaining');
            if ($spendable < $points) {
                throw new \RuntimeException('Insufficient points balance.');
            }

            // Consume oldest earns first.
            $left = $points;
            $earns = $account->transactions()->liveEarns()->orderBy('earned_at')->orderBy('id')->get();
            foreach ($earns as $earn) {
                if ($left <= 0) {
                    break;
                }
                $take = min($left, (int) $earn->remaining);
                $earn->forceFill(['remaining' => (int) $earn->remaining - $take])->save();
                $left -= $take;
            }

            $txn = new PointsTransaction([
                'direction' => 'spend',
                'source' => $source,
                'points' => -$points,
                'remaining' => null,
                'description' => $description,
                'status' => 'approved',
            ]);
            $txn->points_account_id = $account->id;
            if ($reference) {
                $txn->reference()->associate($reference);
            }
            $txn->save();

            $this->recompute($account);

            return $txn;
        });
    }

    /** Nightly: expire earns past their 24-month window (FIFO keeps remaining correct). */
    public function expire(): int
    {
        $expired = 0;
        $earns = PointsTransaction::query()->liveEarns()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($earns as $earn) {
            DB::transaction(function () use ($earn, &$expired) {
                $amount = (int) $earn->remaining;
                if ($amount <= 0) {
                    return;
                }
                $exp = new PointsTransaction([
                    'direction' => 'expire',
                    'source' => 'expiry',
                    'points' => -$amount,
                    'remaining' => null,
                    'description' => 'Points expired (24-month limit).',
                    'status' => 'expired',
                ]);
                $exp->points_account_id = $earn->points_account_id;
                $exp->reference()->associate($earn);
                $exp->save();

                $earn->forceFill(['remaining' => 0])->save();
                $this->recompute($earn->account);
                $expired += $amount;
            });
        }

        return $expired;
    }

    /** Clawback (deleted post, refunded project): reverse the unconsumed remainder of an earn. */
    public function reverse(PointsTransaction $earn, string $reason): PointsTransaction
    {
        return DB::transaction(function () use ($earn, $reason) {
            $remaining = (int) $earn->remaining;

            $rev = new PointsTransaction([
                'direction' => 'reverse',
                'source' => 'reversal',
                'points' => -$earn->points,
                'remaining' => null,
                'description' => $reason,
                'status' => 'reversed',
            ]);
            $rev->points_account_id = $earn->points_account_id;
            $rev->reference()->associate($earn);
            $rev->save();

            // Exclude the earn from lifetime/balance entirely.
            $earn->forceFill(['status' => 'reversed', 'remaining' => 0])->save();
            $this->recompute($earn->account);

            return $rev;
        });
    }

    /** Recompute the cached balance / lifetime / tier from the ledger. */
    public function recompute(PointsAccount $account): void
    {
        $approvedEarns = $account->transactions()
            ->where('direction', 'earn')->where('status', 'approved');

        $lifetime = (int) (clone $approvedEarns)->sum('points');
        $balance = (int) (clone $approvedEarns)->sum('remaining');

        $account->forceFill([
            'lifetime_points' => $lifetime,
            'balance' => max(0, $balance),
        ])->save();

        $this->tiers->recompute($account);
    }

    /** Map an earning-rule key to the ledger `source` vocabulary. */
    private function sourceForRule(string $key): string
    {
        return match (true) {
            str_starts_with($key, 'idea') => 'idea',
            str_starts_with($key, 'social') => 'social',
            $key === 'review_public' => 'review',
            $key === 'event_attendance' => 'event',
            $key === 'profile_complete' => 'profile',
            $key === 'survey_nps' => 'survey',
            $key === 'referral_signup' => 'referral_signup',
            str_starts_with($key, 'referral_payment') => 'referral_payment',
            default => $key,
        };
    }
}
