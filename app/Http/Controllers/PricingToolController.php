<?php

namespace App\Http\Controllers;

use App\Models\PricingRule;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PricingToolController extends Controller
{
    /**
     * Show pricing tool for employees (quoting)
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $rules = PricingRule::active()->orderBy('item')->orderBy('level')->get();

        return view('pricing.tool', compact('rules'));
    }

    /**
     * Admin view - manage pricing rules
     */
    public function admin()
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        $rules = PricingRule::orderBy('item')->orderBy('level')->get();

        return view('pricing.admin', compact('rules'));
    }

    /**
     * Store new pricing rule (admin)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'level' => 'required|string|max:100',
            'note' => 'required|string',
        ]);

        PricingRule::create($validated);

        return back()->with('success', 'Pricing rule added successfully!');
    }

    /**
     * Update pricing rule (admin)
     */
    public function update(Request $request, PricingRule $rule)
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'item' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'level' => 'required|string|max:100',
            'note' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $rule->update($validated);

        return back()->with('success', 'Pricing rule updated successfully!');
    }

    /**
     * Delete pricing rule (admin)
     */
    public function destroy(PricingRule $rule)
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        $rule->delete();

        return back()->with('success', 'Pricing rule deleted successfully!');
    }

    /**
     * Get all active rules as JSON (for employee tool)
     */
    public function getRules()
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        return response()->json(PricingRule::active()->orderBy('item')->orderBy('level')->get());
    }

    /**
     * Show projects awaiting quotation (planning phase assigned to user)
     */
    public function quotingTasks()
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        // Get planning projects assigned to this user
        $projects = Project::where('status', 'planning')
            ->whereHas('projectPeople', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['client', 'quotedBy', 'projectPeople.user'])
            ->latest()
            ->paginate(12);

        return view('pricing.quoting-tasks', compact('projects'));
    }

    /**
     * Upload/Update quote file for a project
     */
    public function uploadQuote(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Check if user is assigned to this project
        if (!$project->projectPeople()->where('user_id', $user->id)->exists() && !$user->isManager()) {
            abort(403, 'You are not assigned to this project.');
        }

        $validated = $request->validate([
            'quote_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'notes' => 'nullable|string',
        ]);

        // Delete old quote file if exists
        if ($project->quote_file) {
            Storage::disk('private')->delete($project->quote_file);
        }

        // Store new quote file
        $path = $request->file('quote_file')->store('quotes', 'private');

        $project->update([
            'quote_file' => $path,
            'quoted_by' => $user->id,
            'quoted_at' => now(),
        ]);

        return redirect()->route('pricing.quoting-tasks')
            ->with('success', 'Quote uploaded successfully!');
    }

    /**
     * Download quote file
     */
    public function downloadQuote(Project $project)
    {
        $user = Auth::user();
        
        if (!$project->quote_file) {
            abort(404, 'No quote file found.');
        }

        // Check permission
        if (!$project->projectPeople()->where('user_id', $user->id)->exists() && !$user->isManager()) {
            abort(403);
        }

        return Storage::disk('private')->download($project->quote_file, "Quote-Project-{$project->id}.pdf");
    }
}
