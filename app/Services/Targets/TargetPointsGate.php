<?php

namespace App\Services\Targets;

use App\Models\PerformanceTarget;
use App\Models\TargetMetric;
use App\Models\User;
use App\Services\EngagementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * The targets↔points link (per the locked decision): an activity earns impact
 * points only AFTER its monthly target is hit — i.e. for units strictly beyond
 * the target. If no target is set for that metric, the activity is ungated and
 * awards normally. Call AFTER the activity has been persisted (so the live count
 * already includes it).
 */
class TargetPointsGate
{
    public function __construct(private ActualsService $actuals) {}

    /** Whether the given impact-points action should award for this user right now. */
    public function shouldAward(User $user, string $action): bool
    {
        $metric = TargetMetric::byEngagementAction($action);
        if (! $metric) {
            return true; // no governing metric → not gated
        }

        $period = now()->startOfMonth();
        $target = PerformanceTarget::where('user_id', $user->id)
            ->whereDate('period', $period)
            ->where('target_metric_id', $metric->id)
            ->first();

        if (! $target) {
            return true; // no target set → award normally
        }

        // Award only once actual is strictly beyond the target (over-achievement).
        $actual = $this->actuals->actualFor($user, $metric, $period);

        return $actual > (float) $target->target_value;
    }

    /**
     * Award an impact-points action to $user only if the gate allows it. Toast is
     * silenced when the credited user isn't the current actor. Call AFTER the
     * activity is persisted. Best-effort: never throws into the request path.
     */
    public function awardIfEarned(User $user, string $action, ?Model $subject = null, ?string $description = null): void
    {
        try {
            if (! $this->shouldAward($user, $action)) {
                return;
            }
            $silent = Auth::id() !== $user->id;
            app(EngagementService::class)->award($user, $action, $subject, null, $description, $silent);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
