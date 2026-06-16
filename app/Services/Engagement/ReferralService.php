<?php

namespace App\Services\Engagement;

use App\Engagement\Contracts\BillingBridge;
use App\Models\PointsAccount;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Referral program (spec §10): unique code per account, signup attribution,
 * two-step one-time reward (10 on signup + 50/100 on the referred client's
 * first paid project), a welcome perk for the referred client, and anti-abuse.
 */
class ReferralService
{
    public function __construct(
        private PointsService $points,
        private BillingBridge $billing,
    ) {}

    /** Stash a referral code from a share link into the session, for use at signup. */
    public function rememberCode(string $code): void
    {
        try {
            session()->put('referral_code', trim($code));
        } catch (\Throwable $e) {
            // no session (console) — nothing to remember
        }
    }

    private function rememberedCode(): ?string
    {
        try {
            $code = session()->get('referral_code');

            return $code ? trim($code) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * On a new client account: record the referral attribution (status 'pending').
     * The signup reward + welcome perk vest only when the referred client VERIFIES
     * their email (confirmSignup) — this blunts throwaway-account farming, since a
     * farmer must control and verify each email.
     */
    public function attributeSignup(User $newClient): void
    {
        $code = $this->rememberedCode();
        if (! $code) {
            return;
        }

        $referrer = PointsAccount::where('referral_code', $code)->first();
        if (! $referrer) {
            return;
        }

        // Anti-abuse: a referrer cannot refer themselves (same account or same email).
        $referrerEmail = strtolower(trim((string) $referrer->client?->email));
        $referredEmail = strtolower(trim((string) $newClient->email));
        if ($referrer->client_id === $newClient->id || ($referrerEmail !== '' && $referrerEmail === $referredEmail)) {
            return;
        }

        // One referral row per referred client.
        if (Referral::where('referred_client_id', $newClient->id)->exists()) {
            return;
        }

        Referral::create([
            'referrer_account_id' => $referrer->id,
            'referred_client_id' => $newClient->id,
            'referred_email' => $newClient->email,
            'code_used' => $code,
            'status' => 'pending',
        ]);

        try {
            session()->forget('referral_code');
        } catch (\Throwable $e) {
            // ignore
        }

        // If the account is already verified at creation (admin-created / OAuth), vest now.
        if ($newClient->hasVerifiedEmail()) {
            $this->confirmSignup($newClient);
        }
    }

    /** Vest the signup reward + welcome perk once the referred client verifies their email. */
    public function confirmSignup(User $client): void
    {
        if (! $client->isClient()) {
            return;
        }
        $pending = Referral::where('referred_client_id', $client->id)
            ->where('status', 'pending')
            ->whereNull('signup_rewarded_at')
            ->first();
        if (! $pending) {
            return;
        }

        DB::transaction(function () use ($pending, $client) {
            $referral = Referral::lockForUpdate()->find($pending->id);
            if (! $referral || $referral->status !== 'pending' || $referral->signup_rewarded_at) {
                return; // already vested by a concurrent call
            }

            $this->points->awardByRule($referral->referrer, 'referral_signup', $referral, 'Referral signup: '.$client->name);
            $referral->update(['status' => 'signed_up', 'signup_rewarded_at' => now()]);

            // Two-sided: the referred client gets starter points.
            $welcome = (int) config('engagement_points.welcome_points', 0);
            if ($welcome > 0) {
                $this->points->awardPoints($this->points->accountFor($client), $welcome, 'welcome', $referral, 'Welcome bonus');
            }
        });
    }

    /** When a referred client's project is paid in full, reward the referrer once. */
    public function rewardPaymentIfReferred(User $client, int $projectId): void
    {
        $candidate = Referral::where('referred_client_id', $client->id)
            ->whereIn('status', ['signed_up', 'qualified'])
            ->whereNull('payment_rewarded_at')
            ->first();
        if (! $candidate) {
            return;
        }

        $value = $this->billing->projectValue($projectId);
        if ($value < (float) config('engagement_points.referral_min_qualifying', 0)) {
            return; // too small to qualify
        }

        $threshold = (float) config('engagement_points.referral_value_threshold', 20000);
        $ruleKey = $value >= $threshold ? 'referral_payment_large' : 'referral_payment_small';

        DB::transaction(function () use ($candidate, $ruleKey, $projectId) {
            // Lock + re-check so two concurrent paid-in-full triggers can't double-award.
            $referral = Referral::lockForUpdate()->find($candidate->id);
            if (! $referral || $referral->payment_rewarded_at || ! in_array($referral->status, ['signed_up', 'qualified'], true)) {
                return;
            }
            $this->points->awardByRule($referral->referrer, $ruleKey, $referral, 'Referral first payment');
            $referral->update([
                'status' => 'rewarded',
                'qualifying_project_id' => $projectId,
                'payment_rewarded_at' => now(),
            ]);
        });
    }
}
