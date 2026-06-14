<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Mail\ScopeChangeDecision;
use App\Mail\ScopeChangeRequested;
use App\Models\Project;
use App\Models\ProjectScopeChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ScopeChangeController extends Controller
{
    /**
     * Show form for client to request scope change
     */
    public function create(Project $project)
    {
        $user = Auth::user();

        if (! $user->canUseClientProjectPortal() || $project->client_id !== $user->id) {
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

        if (! $user->canUseClientProjectPortal() || $project->client_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'justification' => 'nullable|string',
            'budget_delta' => 'nullable|numeric',
        ]);

        $scopeChange = ProjectScopeChange::create([
            ...$validated,
            'budget_delta' => $validated['budget_delta'] ?? 0,
            'project_id' => $project->id,
            'requested_by' => $user->id,
            'status' => 'pending',
        ]);

        $managerEmail = $project->projectManager?->email;
        if ($managerEmail) {
            Mail::to($managerEmail)->send(new ScopeChangeRequested($scopeChange));
        }

        return redirect()->route('projects.client.show', $project)
            ->with('success', 'Scope change request submitted! Waiting for manager review.');
    }

    /**
     * Show all scope change requests (manager)
     */
    public function index()
    {
        $user = Auth::user();

        if (! $user->isManager()) {
            abort(403);
        }

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

        $this->authorize('update', $scopeChange);

        $validated = $request->validate([
            'review_notes' => 'nullable|string',
        ]);

        $scopeChange->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        $clientEmail = $scopeChange->project->client?->email;
        if ($clientEmail) {
            Mail::to($clientEmail)->send(new ScopeChangeDecision($scopeChange->fresh()));
        }

        return back()->with('success', 'Scope change approved!');
    }

    /**
     * Reject scope change
     */
    public function reject(Request $request, ProjectScopeChange $scopeChange)
    {
        $user = Auth::user();

        $this->authorize('update', $scopeChange);

        $validated = $request->validate([
            'review_notes' => 'required|string',
        ]);

        $scopeChange->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
        ]);

        $clientEmail = $scopeChange->project->client?->email;
        if ($clientEmail) {
            Mail::to($clientEmail)->send(new ScopeChangeDecision($scopeChange->fresh()));
        }

        return back()->with('success', 'Scope change rejected.');
    }

    /** Client: list this project's change requests (and sign the approved ones). */
    public function clientList(Project $project)
    {
        $user = Auth::user();

        if (! $user->canUseClientProjectPortal() || $project->client_id !== $user->id) {
            abort(403);
        }

        $changeRequests = $project->scopeChanges()->with('requestedBy')->latest()->get();

        return view('projects.client.change-requests', compact('project', 'changeRequests'));
    }

    /**
     * Client digitally signs an approved change request. Signing is what applies
     * the budget delta to the (otherwise locked) project budget.
     */
    public function sign(Request $request, ProjectScopeChange $scopeChange)
    {
        $user = Auth::user();
        $project = $scopeChange->project;

        if (! $user->canUseClientProjectPortal() || $project->client_id !== $user->id) {
            abort(403);
        }

        if (! $scopeChange->needsClientSignature()) {
            return back()->withErrors(['sign' => 'This change request is not awaiting your signature.']);
        }

        $validated = $request->validate([
            'client_signature' => 'required|string|max:120',
        ]);

        DB::transaction(function () use ($scopeChange, $project, $validated, $request) {
            // The signature is the formal approval that unlocks the budget change.
            $project->allowBudgetOverride = true;
            $project->budget = max(0, (float) $project->budget + (float) $scopeChange->budget_delta);
            $project->save();

            $scopeChange->update([
                'client_signature' => $validated['client_signature'],
                'client_signed_at' => now(),
                'client_ip' => $request->ip(),
                'applied_at' => now(),
            ]);
        });

        return back()->with('success', 'Change request signed — the revised budget is now in effect.');
    }
}
