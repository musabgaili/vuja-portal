<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Project $project)
    {
        $user = Auth::user();
        
        if (!$project->canUserView($user) || !$user->isInternal()) {
            abort(403);
        }

        $expenses = $project->expenses()->with('loggedBy')->latest()->paginate(15);
        $totalExpenses = $project->expenses()->sum('amount');

        return view('projects.manager.expenses', compact('project', 'expenses', 'totalExpenses'));
    }

    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        if (!$project->canUserManageExpenses($user)) {
            abort(403, 'You do not have permission to manage expenses.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'category' => 'nullable|string',
            'expense_date' => 'required|date',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('receipt_file')) {
            $validated['receipt_file'] = $request->file('receipt_file')->store('receipts', 'private');
        }

        $validated['project_id'] = $project->id;
        $validated['logged_by'] = $user->id;

        ProjectExpense::create($validated);

        // Update project spent amount
        $project->update([
            'spent' => $project->expenses()->sum('amount')
        ]);

        return back()->with('success', 'Expense logged successfully!');
    }

    public function destroy(ProjectExpense $expense)
    {
        $user = Auth::user();
        
        if (!$expense->project->canUserManageExpenses($user)) {
            abort(403, 'You do not have permission to manage expenses.');
        }

        $project = $expense->project;
        $expense->delete();

        // Update project spent amount
        $project->update([
            'spent' => $project->expenses()->sum('amount')
        ]);

        return back()->with('success', 'Expense deleted successfully!');
    }
}
