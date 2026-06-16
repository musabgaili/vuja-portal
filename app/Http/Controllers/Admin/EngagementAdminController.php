<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EarningRule;
use App\Models\EngagementClaim;
use App\Models\PointGrantRequest;
use App\Models\PointsAccount;
use App\Models\PointsTransaction;
use App\Models\Redemption;
use App\Models\RedemptionOption;
use App\Models\Referral;
use App\Models\Tier;
use App\Services\Engagement\PointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Admin module (spec §14): configure earning rules / redemption options / tiers,
 * view client accounts + ledgers, manually adjust, and the liability report.
 * Manager-only via the manage-engagement gate.
 */
class EngagementAdminController extends Controller
{
    public function __construct(private PointsService $points) {}

    private function guard(): void
    {
        abort_unless(Gate::allows('manage-engagement'), 403);
    }

    public function dashboard()
    {
        $this->guard();

        $approvedEarns = PointsTransaction::where('direction', 'earn')->where('status', 'approved');

        $stats = [
            'accounts' => PointsAccount::count(),
            'liability' => (int) (clone $approvedEarns)->sum('remaining'),
            'lifetime_awarded' => (int) (clone $approvedEarns)->sum('points'),
            'pending_claims' => EngagementClaim::where('status', 'submitted')->count(),
            'pending_grants' => PointGrantRequest::where('status', 'suggested')->count(),
            'redemptions' => Redemption::count(),
        ];

        $topAdvocates = PointsAccount::with('client', 'tier')->orderByDesc('lifetime_points')->take(10)->get();

        return view('engagement.admin.dashboard', compact('stats', 'topAdvocates'));
    }

    public function config()
    {
        $this->guard();

        return view('engagement.admin.config', [
            'rules' => EarningRule::orderBy('sort_order')->get(),
            'options' => RedemptionOption::orderBy('sort_order')->get(),
            'tiers' => Tier::orderBy('min_lifetime_points')->get(),
        ]);
    }

    public function updateRule(Request $request, EarningRule $rule)
    {
        $this->guard();
        $data = $request->validate([
            'points' => 'required|integer|min:0|max:100000',
            'cap_count' => 'nullable|integer|min:0|max:100000',
            'cap_period' => 'nullable|in:month,quarter,year,lifetime',
        ]);

        $rule->update([
            'points' => $data['points'],
            'is_active' => $request->boolean('is_active'),
            'auto_award' => $request->boolean('auto_award'),
            'requires_review' => $request->boolean('requires_review'),
            'cap_count' => $data['cap_count'] ?? null,
            'cap_period' => $data['cap_period'] ?? null,
        ]);

        return back()->with('success', __('engagement.admin.rule_saved'));
    }

    public function updateRedemption(Request $request, RedemptionOption $option)
    {
        $this->guard();
        $data = $request->validate([
            'cost_points' => 'required|integer|min:0|max:100000',
            'percent' => 'nullable|numeric|min:0|max:100',
            'cap_sar' => 'nullable|numeric|min:0|max:1000000',
            'days' => 'nullable|integer|min:0|max:3650',
            'minutes' => 'nullable|integer|min:0|max:1440',
        ]);

        $meta = $option->value_meta ?? [];
        if ($option->type === 'service_discount') {
            if ($request->filled('percent')) {
                $meta['percent'] = (float) $data['percent'];
            }
            if ($request->filled('cap_sar')) {
                $meta['cap_sar'] = (float) $data['cap_sar'];
            }
        } elseif ($option->type === 'ai_access' && $request->filled('days')) {
            $meta['days'] = (int) $data['days'];
        } elseif ($option->type === 'consultation' && $request->filled('minutes')) {
            $meta['minutes'] = (int) $data['minutes'];
        }

        $option->update([
            'cost_points' => $data['cost_points'],
            'is_active' => $request->boolean('is_active'),
            'value_meta' => $meta,
        ]);

        return back()->with('success', __('engagement.admin.option_saved'));
    }

    public function updateTier(Request $request, Tier $tier)
    {
        $this->guard();
        $data = $request->validate([
            'name' => 'required|string|max:60',
            'name_ar' => 'nullable|string|max:60',
            'min_lifetime_points' => 'required|integer|min:0|max:1000000',
        ]);
        $tier->update($data);

        return back()->with('success', __('engagement.admin.tier_saved'));
    }

    public function accounts(Request $request)
    {
        $this->guard();
        $search = $request->get('q');

        $accounts = PointsAccount::with('client', 'tier')
            ->when($search, fn ($q) => $q->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
            ->orderByDesc('lifetime_points')
            ->paginate(20)->withQueryString();

        return view('engagement.admin.accounts', compact('accounts', 'search'));
    }

    public function account(PointsAccount $account)
    {
        $this->guard();
        $account->load('client', 'tier');
        $transactions = $account->transactions()->with('reference', 'creator')->paginate(25);

        return view('engagement.admin.account', compact('account', 'transactions'));
    }

    public function adjust(Request $request, PointsAccount $account)
    {
        $this->guard();
        $data = $request->validate([
            'points' => 'required|integer|min:-100000|max:100000|not_in:0',
            'reason' => 'required|string|max:500',
        ]);

        if ($data['points'] > 0) {
            $this->points->awardPoints($account, $data['points'], 'manual_grant', null, $data['reason'], false, Auth::id());

            return back()->with('success', __('engagement.admin.adjusted'));
        }

        $deduct = min(abs($data['points']), (int) $account->balance);
        if ($deduct <= 0) {
            return back()->withErrors(['points' => __('engagement.admin.nothing_to_deduct')]);
        }
        $this->points->spend($account, $deduct, 'reversal', null, $data['reason']);

        return back()->with('success', __('engagement.admin.adjusted'));
    }

    public function report()
    {
        $this->guard();
        $approvedEarns = PointsTransaction::where('direction', 'earn')->where('status', 'approved');

        $report = [
            'liability_points' => (int) (clone $approvedEarns)->sum('remaining'),
            'lifetime_awarded' => (int) (clone $approvedEarns)->sum('points'),
            'spent' => (int) abs(PointsTransaction::where('direction', 'spend')->sum('points')),
            'expired' => (int) abs(PointsTransaction::where('direction', 'expire')->sum('points')),
            'redemptions_by_type' => Redemption::selectRaw('redemption_options.type, count(*) as n, sum(redemptions.cost_points) as pts')
                ->join('redemption_options', 'redemption_options.id', '=', 'redemptions.redemption_option_id')
                ->groupBy('redemption_options.type')->get(),
            'referrals_total' => Referral::count(),
            'referrals_rewarded' => Referral::where('status', 'rewarded')->count(),
        ];
        $topAdvocates = PointsAccount::with('client', 'tier')->orderByDesc('lifetime_points')->take(15)->get();

        return view('engagement.admin.report', compact('report', 'topAdvocates'));
    }
}
