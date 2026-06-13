<?php

namespace App\Services;

use App\Models\EngagementLog;
use App\Models\ThankYouToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Central engine for the "Impact Points" engagement system.
 * Awards points, maintains the denormalized user total, surfaces a real-time
 * toast, and powers the owner's engagement dashboard (leaderboard / burnout /
 * hidden gem). All point values come from config/engagement.php.
 */
class EngagementService
{
    /**
     * Award points for an action. Points are looked up from config unless
     * $overridePoints is given. Returns the created log (or null if no-op).
     */
    public function award(User $user, string $action, ?Model $subject = null, ?int $overridePoints = null, ?string $description = null): ?EngagementLog
    {
        $points = $overridePoints ?? (config("engagement.actions.$action"));
        if ($points === null) {
            return null; // unknown action — fail safe, never throw in a request path
        }

        $log = DB::transaction(function () use ($user, $action, $points, $subject, $description) {
            $log = new EngagementLog([
                'action' => $action,
                'points' => $points,
                'description' => $description,
            ]);
            $log->user()->associate($user);
            if ($subject) {
                $log->subject()->associate($subject);
            }
            $log->save();

            // Keep the running total non-negative.
            $user->impact_points = max(0, (int) $user->impact_points + $points);
            $user->save();

            return $log;
        });

        // Real-time toast (consumed by the layout) — best-effort, never blocks.
        if (function_exists('session')) {
            $toasts = session()->get('ip_toasts', []);
            $toasts[] = ['points' => $points, 'action' => $action];
            session()->flash('ip_toasts', $toasts);
        }

        return $log;
    }

    /** Resolve the level definition for a points total. */
    public function levelFor(int $points): array
    {
        foreach (config('engagement.levels') as $i => $level) {
            if ($points >= $level['min'] && $points <= $level['max']) {
                return $level + ['index' => $i];
            }
        }
        $levels = config('engagement.levels');

        return $levels[0] + ['index' => 0];
    }

    /** Progress (0-100) toward the next level for the XP bar. */
    public function levelProgress(int $points): int
    {
        $level = $this->levelFor($points);
        if ($level['max'] === PHP_INT_MAX) {
            return 100;
        }
        $span = $level['max'] - $level['min'] + 1;
        $into = $points - $level['min'];

        return (int) round(min(100, max(0, ($into / $span) * 100)));
    }

    /** Record a peer Thank-You token (enforces the monthly quota). */
    public function recordThankYou(User $from, User $to, ?string $message = null): array
    {
        if ($from->id === $to->id) {
            return ['ok' => false, 'error' => 'You cannot thank yourself.'];
        }
        $period = now()->format('Y-m');
        $used = ThankYouToken::where('from_user_id', $from->id)->where('period', $period)->count();
        $quota = (int) config('engagement.thank_you_monthly_quota', 5);
        if ($used >= $quota) {
            return ['ok' => false, 'error' => "You've used all {$quota} Thank-You tokens this month."];
        }

        ThankYouToken::create([
            'from_user_id' => $from->id,
            'to_user_id' => $to->id,
            'message' => $message,
            'period' => $period,
        ]);
        $this->award($to, 'thank_you_received', $from, null, "Thanked by {$from->name}");

        return ['ok' => true, 'remaining' => $quota - $used - 1];
    }

    public function thankYouRemaining(User $user): int
    {
        $period = now()->format('Y-m');
        $used = ThankYouToken::where('from_user_id', $user->id)->where('period', $period)->count();

        return max(0, (int) config('engagement.thank_you_monthly_quota', 5) - $used);
    }

    /** Leaderboard: most valuable culture-carriers (by impact points). */
    public function leaderboard(int $limit = 10)
    {
        return User::where('type', 'internal')
            ->orderByDesc('impact_points')
            ->limit($limit)
            ->get()
            ->map(fn (User $u) => [
                'user' => $u,
                'points' => (int) $u->impact_points,
                'level' => $this->levelFor((int) $u->impact_points),
            ]);
    }

    /**
     * Burnout alerts: high scorers (top tier) with no engagement activity
     * for config(burnout_inactive_days).
     */
    public function burnoutAlerts()
    {
        $cutoff = now()->subDays((int) config('engagement.burnout_inactive_days', 7));
        $threshold = config('engagement.levels')[2]['min'] ?? 2001; // Client Champion+

        return User::where('type', 'internal')
            ->where('impact_points', '>=', $threshold)
            ->get()
            ->filter(function (User $u) use ($cutoff) {
                $last = EngagementLog::where('user_id', $u->id)->max('created_at');

                return $last === null || $last < $cutoff;
            })
            ->values();
    }

    /**
     * Hidden gems: quietly helpful people — high collaboration (solution
     * comments + peer reviews + thank-yous) but not top of the raw leaderboard.
     */
    public function hiddenGems(int $limit = 5)
    {
        $helpScores = EngagementLog::whereIn('action', ['solution_comment', 'peer_review', 'thank_you_received'])
            ->select('user_id', DB::raw('SUM(points) as help_points'))
            ->groupBy('user_id')
            ->orderByDesc('help_points')
            ->limit($limit)
            ->get();

        return $helpScores->map(fn ($row) => [
            'user' => User::find($row->user_id),
            'help_points' => (int) $row->help_points,
        ])->filter(fn ($r) => $r['user'] !== null)->values();
    }
}
