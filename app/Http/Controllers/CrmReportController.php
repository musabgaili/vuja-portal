<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\Auth;

class CrmReportController extends Controller
{
    /** Sales analytics: pipeline, win rate, forecast, per-rep performance. */
    public function index()
    {
        abort_unless(Auth::user()->isManager(), 403);

        $stages = PipelineStage::map();
        $all = Opportunity::with('owner')->get();
        $open = $all->whereIn('stage', array_keys($stages));
        $won = $all->where('stage', 'won');
        $lost = $all->where('stage', 'lost');

        $closed = $won->count() + $lost->count();
        $winRate = $closed ? (int) round($won->count() / $closed * 100) : 0;

        // Pipeline by stage (funnel).
        $byStage = [];
        $maxStageValue = 1;
        foreach ($stages as $key => $label) {
            $items = $open->where('stage', $key);
            $byStage[$key] = ['label' => $label, 'count' => $items->count(), 'value' => (float) $items->sum('expected_value')];
            $maxStageValue = max($maxStageValue, $byStage[$key]['value']);
        }

        // Weighted forecast by expected-close month (open deals).
        $forecast = [];
        foreach ($open as $o) {
            if ($o->expected_close_date) {
                $m = $o->expected_close_date->format('Y-m');
                $forecast[$m] = ($forecast[$m] ?? 0) + $o->weightedValue();
            }
        }
        ksort($forecast);

        // Won value by month.
        $wonByMonth = [];
        foreach ($won as $o) {
            $d = $o->won_at ?? $o->updated_at;
            $m = $d->format('Y-m');
            $wonByMonth[$m] = ($wonByMonth[$m] ?? 0) + (float) $o->expected_value;
        }
        ksort($wonByMonth);

        // Per-rep performance.
        $perRep = $all->groupBy('owner_id')->map(function ($deals) use ($stages) {
            $w = $deals->where('stage', 'won');
            $l = $deals->where('stage', 'lost');
            $o = $deals->whereIn('stage', array_keys($stages));
            $cl = $w->count() + $l->count();

            return [
                'owner' => $deals->first()->owner,
                'open' => $o->count(),
                'open_value' => (float) $o->sum('expected_value'),
                'won' => $w->count(),
                'won_value' => (float) $w->sum('expected_value'),
                'win_rate' => $cl ? (int) round($w->count() / $cl * 100) : 0,
            ];
        })->filter(fn ($r) => $r['owner'])->sortByDesc('won_value')->values();

        $summary = [
            'open_count' => $open->count(),
            'open_value' => (float) $open->sum('expected_value'),
            'weighted' => (float) $open->sum(fn ($o) => $o->weightedValue()),
            'won_value' => (float) $won->sum('expected_value'),
            'win_rate' => $winRate,
            'won_count' => $won->count(),
            'lost_count' => $lost->count(),
        ];

        return view('crm.reports.index', compact('byStage', 'maxStageValue', 'forecast', 'wonByMonth', 'perRep', 'summary'));
    }
}
