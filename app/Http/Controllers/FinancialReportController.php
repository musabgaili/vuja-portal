<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->isManager()) {
            abort(403);
        }

        $query = Project::with(['client', 'projectManager']);

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search) {
                        $clientQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('projectManager', function ($pmQuery) use ($search) {
                        $pmQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($budgetHealth = $request->input('budget_health')) {
            match ($budgetHealth) {
                'over_budget' => $query->whereNotNull('budget')->whereColumn('spent', '>', 'budget'),
                'within_budget' => $query->whereNotNull('budget')->whereColumn('spent', '<=', 'budget'),
                'no_budget' => $query->whereNull('budget'),
                default => null,
            };
        }

        $summaryProjects = (clone $query)->get();

        $totals = [
            'projects' => $summaryProjects->count(),
            'budget' => $summaryProjects->sum(fn (Project $project) => (float) ($project->budget ?? 0)),
            'spent' => $summaryProjects->sum(fn (Project $project) => (float) ($project->spent ?? 0)),
            'remaining' => $summaryProjects->sum(fn (Project $project) => (float) $project->getBudgetRemaining()),
            'over_budget' => $summaryProjects->filter(fn (Project $project) => $project->isOverBudget())->count(),
        ];
        // Profit margin = (budget - actual cost) / budget. The owner's at-a-glance health.
        $totals['margin'] = $totals['budget'] - $totals['spent'];
        $totals['margin_pct'] = $totals['budget'] > 0 ? round($totals['margin'] / $totals['budget'] * 100, 1) : 0.0;

        $projects = $query->latest()->paginate(15)->withQueryString();

        return view('reports.financial', compact('projects', 'totals'));
    }
}
