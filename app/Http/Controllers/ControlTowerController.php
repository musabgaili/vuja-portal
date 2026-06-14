<?php

namespace App\Http\Controllers;

use App\Services\ProjectHealthService;
use Illuminate\Support\Facades\Auth;

class ControlTowerController extends Controller
{
    public function __construct(private ProjectHealthService $health)
    {
    }

    /** Owner's Control Tower: traffic-light project health + risk alert feed. */
    public function index()
    {
        abort_unless(Auth::user()->isManager(), 403);

        $rows = $this->health->dashboard();

        $counts = [
            'red' => $rows->where('health', 'red')->count(),
            'yellow' => $rows->where('health', 'yellow')->count(),
            'green' => $rows->where('health', 'green')->count(),
        ];

        // Flatten flags into a single risk feed (worst first).
        $alerts = $rows->flatMap(function ($row) {
            return collect($row['flags'])->map(fn ($f) => $f + ['project' => $row['project']]);
        })->sortBy(fn ($a) => $a['severity'] === 'red' ? 0 : 1)->values();

        return view('control-tower.index', compact('rows', 'counts', 'alerts'));
    }
}
