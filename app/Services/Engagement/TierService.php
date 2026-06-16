<?php

namespace App\Services\Engagement;

use App\Models\PointsAccount;
use App\Models\Tier;

/** Status tiers driven by lifetime points (never reduced by spending). Spec §12. */
class TierService
{
    public function tierFor(int $lifetimePoints): ?Tier
    {
        return Tier::where('min_lifetime_points', '<=', $lifetimePoints)
            ->orderByDesc('min_lifetime_points')
            ->first();
    }

    /** Set the account's tier to the highest one its lifetime points qualify for. */
    public function recompute(PointsAccount $account): void
    {
        $tier = $this->tierFor((int) $account->lifetime_points);
        $tierId = $tier?->id;

        if ($account->tier_id !== $tierId) {
            $account->forceFill(['tier_id' => $tierId])->save();
        }
    }

    /** The next tier up, for "progress to next" displays. */
    public function nextTier(int $lifetimePoints): ?Tier
    {
        return Tier::where('min_lifetime_points', '>', $lifetimePoints)
            ->orderBy('min_lifetime_points')
            ->first();
    }
}
