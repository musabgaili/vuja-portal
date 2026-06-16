<?php

namespace App\Http\Controllers;

use App\Models\EarningRule;
use App\Models\EngagementClaim;
use App\Models\Quote;
use App\Models\Redemption;
use App\Models\RedemptionOption;
use App\Models\Tier;
use App\Models\User;
use App\Services\Engagement\ClaimService;
use App\Services\Engagement\EarningEngine;
use App\Services\Engagement\PointsService;
use App\Services\Engagement\RedemptionService;
use App\Services\Engagement\TierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Client-facing Engagement Points surfaces (spec §15): the points dashboard
 * (balance / tier / ledger / vouchers / referral / redeem / claim) and the
 * bilingual explainer page. Clients only ever see their own account.
 */
class ClientEngagementController extends Controller
{
    public function __construct(
        private PointsService $points,
        private RedemptionService $redemptions,
        private ClaimService $claims,
        private TierService $tiers,
        private EarningEngine $earning,
    ) {}

    private function guardClient(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->canUseClientProjectPortal(), 403);

        return $user;
    }

    public function dashboard()
    {
        $user = $this->guardClient();
        $this->earning->recordProfileComplete($user); // one-time, idempotent

        $account = $this->points->accountFor($user)->fresh(['tier']);
        $nextTier = $this->tiers->nextTier((int) $account->lifetime_points);
        $transactions = $account->transactions()->with('reference')->paginate(15);
        $vouchers = $account->redemptions()->with('option')->whereIn('status', ['issued', 'applied'])->get();
        $referrals = $account->referrals()->with('referredClient')->get();
        $options = RedemptionOption::active()->orderBy('sort_order')->get();
        $claimableRules = EarningRule::active()
            ->whereIn('key', config('engagement_points.claimable_rules', []))
            ->orderBy('sort_order')->get();
        $claims = $account->claims()->take(10)->get();

        // Quotes the client may still apply a discount voucher to: awaiting their
        // acceptance, no discount yet, and have at least one service line.
        $eligibleQuotes = Quote::where('client_id', $user->id)
            ->where('status', 'sent')
            ->where('discount_amount', '<=', 0)
            ->get()
            ->filter(fn ($q) => $q->items()->whereIn('type', ['service', 'other'])->exists())
            ->values();

        return view('engagement.dashboard', compact(
            'account', 'nextTier', 'transactions', 'vouchers', 'referrals',
            'options', 'claimableRules', 'claims', 'eligibleQuotes'
        ));
    }

    public function about()
    {
        $user = $this->guardClient();
        $account = $this->points->accountFor($user);

        return view('engagement.about', [
            'account' => $account,
            'rules' => EarningRule::active()->orderBy('sort_order')->get(),
            'options' => RedemptionOption::active()->orderBy('sort_order')->get(),
            'tiers' => Tier::orderBy('min_lifetime_points')->get(),
        ]);
    }

    public function redeem(RedemptionOption $option)
    {
        $user = $this->guardClient();
        $account = $this->points->accountFor($user);

        try {
            $this->redemptions->redeem($account, $option);

            return back()->with('success', __('engagement.redeemed', ['name' => $option->localizedName()]));
        } catch (\Throwable $e) {
            return back()->withErrors(['redeem' => $e->getMessage()]);
        }
    }

    public function applyVoucher(Request $request, Redemption $redemption)
    {
        $user = $this->guardClient();
        abort_unless($redemption->account->client_id === $user->id, 403);

        $data = $request->validate(['quote_id' => 'required|integer|exists:quotes,id']);
        $quote = Quote::findOrFail($data['quote_id']);
        abort_unless($quote->client_id === $user->id, 403);

        try {
            $discount = $this->redemptions->applyDiscountToQuote($redemption, $quote);

            return back()->with('success', __('engagement.voucher_applied', ['amount' => number_format($discount, 2)]));
        } catch (\Throwable $e) {
            return back()->withErrors(['voucher' => $e->getMessage()]);
        }
    }

    public function storeClaim(Request $request)
    {
        $user = $this->guardClient();
        $account = $this->points->accountFor($user);

        $claimable = config('engagement_points.claimable_rules', []);
        $data = $request->validate([
            'earning_rule_key' => 'required|string|in:'.implode(',', $claimable),
            'platform' => 'nullable|in:linkedin,x,instagram,google,other',
            'post_url' => 'nullable|url|max:500',
            'posted_at' => 'nullable|date|before_or_equal:today',
            'screenshot' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Sensitive proof on the non-public disk (spec §17).
        $path = $request->hasFile('screenshot')
            ? $request->file('screenshot')->store('engagement-claims', 'private')
            : null;

        try {
            $this->claims->submit($account, $data['earning_rule_key'], [
                'platform' => $data['platform'] ?? null,
                'post_url' => $data['post_url'] ?? null,
                'posted_at' => $data['posted_at'] ?? null,
                'screenshot_path' => $path,
            ]);

            return back()->with('success', __('engagement.claim_submitted'));
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('private')->delete($path);
            }

            return back()->withErrors(['claim' => $e->getMessage()]);
        }
    }

    public function claimScreenshot(EngagementClaim $claim)
    {
        $user = $this->guardClient();
        abort_unless($claim->account->client_id === $user->id, 403);
        abort_unless($claim->screenshot_path && Storage::disk('private')->exists($claim->screenshot_path), 404);

        return Storage::disk('private')->download($claim->screenshot_path);
    }
}
