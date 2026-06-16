<?php

namespace App\Http\Controllers;

use App\Services\Targets\ActualsService;
use App\Services\Targets\TargetService;
use Illuminate\Support\Facades\Auth;

/**
 * "My Targets" — an internal staff member's own monthly targets, live progress,
 * and 6-month trend. Self-only (no IDOR): always reads the authenticated user.
 */
class StaffTargetController extends Controller
{
    public function __construct(private ActualsService $actuals, private TargetService $targets) {}

    public function index()
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal(), 403);

        $month = now()->startOfMonth();
        $summary = $this->actuals->summaryFor($user, $month);

        $trends = [];
        foreach ($summary as $row) {
            $trends[$row->metric->id] = $this->targets->trend($user, $row->metric, (int) config('targets.trend_months', 6));
        }

        return view('targets.my', [
            'user' => $user,
            'summary' => $summary,
            'trends' => $trends,
            'month' => $month,
            'overall' => $this->actuals->overallAttainment($user, $month),
        ]);
    }
}
