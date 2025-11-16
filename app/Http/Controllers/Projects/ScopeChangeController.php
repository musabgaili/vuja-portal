<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectScopeChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScopeChangeController extends Controller
{
    /**
     * Show form for client to request scope change
     */
    public function create(Project $project)
    {
        $user = Auth::user();
        
        if ($project->client_id !== $user->id) {
            abort(403);
        }

        return view('projects.client.scope-change', compact('project'));
    }

    /**
     * Store scope change request from client
     */
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        if ($project->client_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'justification' => 'nullable|string',
        ]);

        ProjectScopeChange::create([
            ...$validated,
            'project_id' => $project->id,
            'requested_by' => $user->id,
            'status' => 'pending',
        ]);

        return redirect()->route('projects.client.show', $project)
            ->with('success', 'Scope change request submitted! Waiting for manager review.');
    }

    /**
     * Show all scope change requests (manager)
     */
    public function index()
    {
        $user = Auth::user();
        
         

        $scopeChanges = ProjectScopeChange::with(['project', 'requestedBy'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('projects.manager.scope-changes', compact('scopeChanges'));
    }

    /**
     * Approve scope change
     */
    public function approve(Request $request, ProjectScopeChange $scopeChange)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'review_notes' => 'nullable|string',
        ]);

        $scopeChange->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return back()->with('success', 'Scope change approved!');
    }

    /**
     * Reject scope change
     */
    public function reject(Request $request, ProjectScopeChange $scopeChange)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'review_notes' => 'required|string',
        ]);

        $scopeChange->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        return back()->with('success', 'Scope change rejected.');
    }
}
