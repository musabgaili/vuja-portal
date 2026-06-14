<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\EngagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manager-only editor for the Impact-Points rulebook: per-action point values
 * (grouped by category), the monthly Thank-You quota, and the burnout window.
 * Overrides are stored in the `settings` table and layered over config defaults
 * by EngagementService — clearing a field reverts that action to its default.
 */
class EngagementSettingsController extends Controller
{
    public function __construct(private EngagementService $engagement)
    {
    }

    public function edit()
    {
        abort_unless(Auth::user()->isManager(), 403);

        return view('engagement.settings', [
            'categories' => config('engagement.categories', []),
            'actionMeta' => config('engagement.action_meta', []),
            'defaults' => config('engagement.actions', []),
            'effective' => $this->engagement->actionPoints(),
            'thankYouQuota' => $this->engagement->thankYouQuota(),
            'burnoutDays' => $this->engagement->burnoutInactiveDays(),
            'thankYouDefault' => (int) config('engagement.thank_you_monthly_quota', 5),
            'burnoutDefault' => (int) config('engagement.burnout_inactive_days', 7),
        ]);
    }

    public function update(Request $request)
    {
        abort_unless(Auth::user()->isManager(), 403);

        $known = array_keys(config('engagement.actions', []));

        $validated = $request->validate([
            'points' => 'array',
            'points.*' => 'nullable|integer|min:-500|max:1000',
            'thank_you_monthly_quota' => 'required|integer|min:0|max:100',
            'burnout_inactive_days' => 'required|integer|min:1|max:90',
        ]);

        // Only persist overrides that differ from the config default; clearing a
        // field (or matching the default) drops the override so it reverts cleanly.
        $defaults = config('engagement.actions', []);
        $overrides = [];
        foreach ($request->input('points', []) as $action => $value) {
            if (! in_array($action, $known, true)) {
                continue; // ignore anything not in the rulebook
            }
            if ($value === null || $value === '') {
                continue;
            }
            if ((int) $value !== (int) ($defaults[$action] ?? PHP_INT_MIN)) {
                $overrides[$action] = (int) $value;
            }
        }

        Setting::put('engagement.actions', $overrides);
        Setting::put('engagement.thank_you_monthly_quota', (int) $validated['thank_you_monthly_quota']);
        Setting::put('engagement.burnout_inactive_days', (int) $validated['burnout_inactive_days']);

        return redirect()->route('engagement.settings.edit')
            ->with('success', __('portal.engagement_settings.saved'));
    }

    public function reset()
    {
        abort_unless(Auth::user()->isManager(), 403);

        Setting::forget('engagement.actions');
        Setting::forget('engagement.thank_you_monthly_quota');
        Setting::forget('engagement.burnout_inactive_days');

        return redirect()->route('engagement.settings.edit')
            ->with('success', __('portal.engagement_settings.reset_done'));
    }
}
