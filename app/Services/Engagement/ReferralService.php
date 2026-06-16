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

    /** On a new client account: attribute the referral, reward the referrer, give a welcome perk. */
    public function attributeSignup(User $newClient): void
    {
        $code = $this->rememberedCode();
        if (! $code) {
            return;
        }

        $referrer = PointsAccount::where('referral_code', $code)->first();
        // Anti-abuse: unknown code or self-referral.
        if (! $referrer || $referrer->client_id === $newClient->id) {
            return;
        }

        // One referral row per referred client.
        if (Referral::where('referred_client_id', $newClient->id)->exists()) {
            return;
        }

        DB::transaction(function () use ($referrer, $newClient, $code) {
            $referral = Referral::create([
                'referrer_account_id' => $referrer->id,
                'referred_client_id' => $newClient->id,
                'referred_email' => $newClient->email,
                'code_used' => $code,
                'status' => 'signed_up',
                'signup_rewarded_at' => now(),
            ]);

            $this->points->awardByRule($referrer, 'referral_signup', $referral, 'Referral signup: '.$newClient->name);

            // Two-sided: the referred client gets starter points.
            $welcome = (int) config('engagement_points.welcome_points', 0);
            if ($welcome > 0) {
                $this->points->awardPoints(
                    $this->points->accountFor($newClient),
                    $welcome,
                    'welcome',
                    $referral,
                    'Welcome bonus',
                );
            }
        });

        try {
            session()->forget('referral_code');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /** When a referred client's project is paid in full, reward the referrer once. */
    public function rewardPaymentIfReferred(User $client, int $projectId): void
    {
        $referral = Referral::where('referred_client_id', $client->id)
            ->whereIn('status', ['signed_up', 'qualified'])
            ->whereNull('payment_rewarded_at')
            ->first();
        if (! $referral) {
            return;
        }

        $value = $this->billing->projectValue($projectId);
        if ($value < (float) config('engagement_points.referral_min_qualifying', 0)) {
            return; // too small to qualify
        }

        $threshold = (float) config('engagement_points.referral_value_threshold', 20000);
        $ruleKey = $value >= $threshold ? 'referral_payment_large' : 'referral_payment_small';

        DB::transaction(function () use ($referral, $ruleKey, $projectId) {
            $this->points->awardByRule($referral->referrer, $ruleKey, $referral, 'Referral first payment');
            $referral->update([
                'status' => 'rewarded',
                'qualifying_project_id' => $projectId,
                'payment_rewarded_at' => now(),
            ]);
        });
    }
}
