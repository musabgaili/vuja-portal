<?php

namespace App\Services\Engagement;

use App\Models\EarningRule;
use App\Models\EngagementClaim;
use App\Models\PointsAccount;
use Illuminate\Support\Facades\DB;

/**
 * Manual-review claims for forgeable actions — social posts, public reviews,
 * event attendance (spec §11). No points until an admin approves.
 */
class ClaimService
{
    public function __construct(private PointsService $points) {}

    /**
     * Client submits a claim. $data: platform, post_url, screenshot_path, posted_at.
     * Throws on a duplicate post URL or a non-claimable rule.
     */
    public function submit(PointsAccount $account, string $ruleKey, array $data): EngagementClaim
    {
        $rule = EarningRule::byKey($ruleKey);
        $claimable = in_array($ruleKey, config('engagement_points.claimable_rules', []), true);
        if (! $rule || ! $rule->is_active || $rule->auto_award || ! $claimable) {
            throw new \RuntimeException('This action cannot be claimed.');
        }

        // Anti-fraud: a given post URL can be claimed once across the program.
        if (! empty($data['post_url'])) {
            $dupe = EngagementClaim::where('post_url', $data['post_url'])
                ->whereIn('status', ['submitted', 'approved'])->exists();
            if ($dupe) {
                throw new \RuntimeException('This post has already been claimed.');
            }
        }

        // Anti-fraud: recency window (spec §11) — no claiming stale posts.
        $recency = (int) config('engagement_points.claim_recency_days', 30);
        if ($recency > 0 && ! empty($data['posted_at'])) {
            $posted = $data['posted_at'] instanceof \DateTimeInterface
                ? \Illuminate\Support\Carbon::instance($data['posted_at'])
                : \Illuminate\Support\Carbon::parse($data['posted_at']);
            if ($posted->lt(now()->subDays($recency))) {
                throw new \RuntimeException('This post is older than the '.$recency.'-day claim window.');
            }
        }

        return EngagementClaim::create([
            'points_account_id' => $account->id,
            'earning_rule_key' => $ruleKey,
            'platform' => $data['platform'] ?? null,
            'post_url' => $data['post_url'] ?? null,
            'screenshot_path' => $data['screenshot_path'] ?? null,
            'posted_at' => $data['posted_at'] ?? null,
            'status' => 'submitted',
        ]);
    }

    /** Admin approves a claim → credit the points and link the resulting transaction. */
    public function approve(EngagementClaim $claim, ?int $reviewerId = null, ?string $notes = null): EngagementClaim
    {
        if (! $claim->isSubmitted()) {
            return $claim;
        }
        $rule = EarningRule::byKey($claim->earning_rule_key);
        if (! $rule) {
            throw new \RuntimeException('Unknown earning rule.');
        }

        return DB::transaction(function () use ($claim, $rule, $reviewerId, $notes) {
            if (! $this->withinCap($claim->account, $rule)) {
                throw new \RuntimeException('The cap for this action has been reached for the period.');
            }

            // Approving the claim IS the review — credit immediately.
            $txn = $this->points->awardByRule($claim->account, $rule->key, $claim, null, false, $reviewerId, true);

            $claim->forceFill([
                'status' => 'approved',
                'review_notes' => $notes,
                'reviewed_by' => $reviewerId,
                'points_transaction_id' => $txn?->id,
            ])->save();

            return $claim;
        });
    }

    public function reject(EngagementClaim $claim, ?int $reviewerId = null, ?string $notes = null): EngagementClaim
    {
        if (! $claim->isSubmitted()) {
            return $claim;
        }
        $claim->forceFill([
            'status' => 'rejected',
            'review_notes' => $notes,
            'reviewed_by' => $reviewerId,
        ])->save();

        return $claim;
    }

    /** Per-period cap (spec §7): count approved claims for this rule in the window. */
    private function withinCap(PointsAccount $account, EarningRule $rule): bool
    {
        if (empty($rule->cap_count)) {
            return true;
        }
        // Bucket by when points were CREDITED (the linked transaction), not when the
        // claim was submitted — otherwise a backlog submitted in one month but
        // approved the next would evade the per-period cap.
        $count = EngagementClaim::where('points_account_id', $account->id)
            ->where('earning_rule_key', $rule->key)
            ->where('status', 'approved')
            ->whereHas('transaction', fn ($q) => $q->where('created_at', '>=', $this->periodStart($rule->cap_period)))
            ->count();

        return $count < $rule->cap_count;
    }

    private function periodStart(?string $period): \Illuminate\Support\Carbon
    {
        return match ($period) {
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->subYears(100), // lifetime
        };
    }
}
