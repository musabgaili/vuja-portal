<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EngagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngagementController extends Controller
{
    public function __construct(private EngagementService $engagement)
    {
    }

    /**
     * Engagement dashboard. Managers see the full Owner view (leaderboard +
     * burnout + hidden gems); employees see their own impact + the leaderboard.
     */
    public function index()
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $isManager = $user->isManager();

        $data = [
            'me' => [
                'points' => (int) $user->impact_points,
                'level' => $this->engagement->levelFor((int) $user->impact_points),
                'progress' => $this->engagement->levelProgress((int) $user->impact_points),
                'recent' => $user->engagementLogs()->latest()->limit(10)->get(),
                'thankYouRemaining' => $this->engagement->thankYouRemaining($user),
            ],
            'leaderboard' => $this->engagement->leaderboard(10),
            'isManager' => $isManager,
            'burnoutAlerts' => $isManager ? $this->engagement->burnoutAlerts() : collect(),
            'hiddenGems' => $isManager ? $this->engagement->hiddenGems(5) : collect(),
            'colleagues' => User::where('type', 'internal')->where('id', '!=', $user->id)->orderBy('name')->get(),
            'badges' => config('engagement.badges'),
        ];

        return view('engagement.index', $data);
    }

    /** Give a peer a Thank-You token (+50 IP to the recipient). */
    public function thankYou(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $validated = $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:255',
        ]);

        $to = User::findOrFail($validated['to_user_id']);
        $result = $this->engagement->recordThankYou($user, $to, $validated['message'] ?? null);

        if (! $result['ok']) {
            return back()->withErrors(['thank_you' => $result['error']]);
        }

        return back()->with('success', "Thank-You sent to {$to->name} (+50 IP). {$result['remaining']} tokens left this month.");
    }
}
