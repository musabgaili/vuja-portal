<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerformanceTarget;
use App\Models\TargetMetric;
use App\Models\User;
use App\Services\Targets\ActualsService;
use App\Services\Targets\TargetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Manager control room for performance targets (spec §14): set/adjust each
 * person's monthly targets, watch live progress with RAG, snapshot the month,
 * drill into a person's 6-month trend, and manage the metric catalog.
 */
class TargetController extends Controller
{
    public function __construct(private ActualsService $actuals, private TargetService $targets) {}

    private function guard(): void
    {
        abort_unless(Auth::user()?->isManager(), 403);
    }

    private function resolveMonth(Request $request): Carbon
    {
        if ($request->filled('month')) {
            try {
                return Carbon::createFromFormat('Y-m', $request->get('month'))->startOfMonth();
            } catch (\Throwable $e) {
                // fall through to current month
            }
        }

        return now()->startOfMonth();
    }

    private function holders()
    {
        return User::where('type', 'internal')
            ->whereIn('role', ['employee', 'project_manager'])
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $this->guard();
        $month = $this->resolveMonth($request);
        $metrics = TargetMetric::active()->orderBy('sort_order')->get();

        $rows = $this->holders()->map(fn (User $u) => (object) [
            'user' => $u,
            'summary' => $this->actuals->summaryFor($u, $month)->keyBy(fn ($r) => $r->metric->id),
            'overall' => $this->actuals->overallAttainment($u, $month),
        ]);

        return view('targets.admin.index', compact('rows', 'metrics', 'month'));
    }

    public function setTarget(Request $request)
    {
        $this->guard();
        $data = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'target_metric_id' => 'required|integer|exists:target_metrics,id',
            'period' => 'required|date',
            'target_value' => 'required|numeric|min:0|max:100000000',
            'current_value' => 'nullable|numeric|min:0|max:100000000',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($data['user_id']);
        abort_unless($user->holdsTargets(), 422);
        $metric = TargetMetric::findOrFail($data['target_metric_id']);

        $this->targets->setTarget(
            $user, $metric, Carbon::parse($data['period']),
            (float) $data['target_value'], $data['notes'] ?? null, Auth::id(),
            isset($data['current_value']) ? (float) $data['current_value'] : null,
        );

        return back()->with('success', __('targets.target_saved'));
    }

    public function deleteTarget(PerformanceTarget $target)
    {
        $this->guard();
        $target->delete();

        return back()->with('success', __('targets.target_removed'));
    }

    public function recordMonth(Request $request)
    {
        $this->guard();
        $data = $request->validate([
            'period' => 'required|date',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);
        $user = ! empty($data['user_id']) ? User::find($data['user_id']) : null;
        $n = $this->targets->recordMonth(Carbon::parse($data['period']), $user, Auth::id());

        return back()->with('success', __('targets.month_recorded', ['n' => $n]));
    }

    public function carryForward(Request $request)
    {
        $this->guard();
        $data = $request->validate(['period' => 'required|date']);
        $n = $this->targets->carryForward(Carbon::parse($data['period']), Auth::id());

        return back()->with('success', __('targets.carried_forward', ['n' => $n]));
    }

    public function show(User $user)
    {
        $this->guard();
        abort_unless($user->isInternal(), 404);

        $month = now()->startOfMonth();
        $summary = $this->actuals->summaryFor($user, $month);
        $months = (int) config('targets.trend_months', 6);

        $trends = [];
        foreach ($summary as $row) {
            $trends[$row->metric->id] = $this->targets->trend($user, $row->metric, $months);
        }

        return view('targets.admin.show', compact('user', 'summary', 'trends', 'month'));
    }

    // ---- Metric catalog ----

    public function metrics()
    {
        $this->guard();

        return view('targets.admin.metrics', ['metrics' => TargetMetric::orderBy('sort_order')->get()]);
    }

    public function storeMetric(Request $request)
    {
        $this->guard();
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'name_ar' => 'nullable|string|max:80',
            'unit' => 'required|in:count,sar',
        ]);
        $key = Str::slug($data['name'], '_') ?: 'metric_'.Str::random(4);

        TargetMetric::firstOrCreate(['key' => $key], [
            'name' => $data['name'],
            'name_ar' => $data['name_ar'] ?? null,
            'source' => 'manual',   // manager-added metrics are manually measured
            'unit' => $data['unit'],
            'is_active' => true,
            'sort_order' => (int) (TargetMetric::max('sort_order') + 1),
        ]);

        return back()->with('success', __('targets.metric_saved'));
    }

    public function updateMetric(Request $request, TargetMetric $metric)
    {
        $this->guard();
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'name_ar' => 'nullable|string|max:80',
        ]);
        $metric->update([
            'name' => $data['name'],
            'name_ar' => $data['name_ar'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', __('targets.metric_saved'));
    }
}
