<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

use App\Models\IdeaRequest;
use App\Models\IdeaRequestComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IdeaRequestController extends Controller
{
    // CLIENT SIDE METHODS
    
    /**
     * Show the form for creating a new idea request.
     */
    public function create()
    {
        return view('ideas.create');
    }

    /**
     * Store a newly created idea request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:individual,company',
            'idea_status' => 'required|in:seeking_around,ready,running_project,concept_only',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'target_market' => 'nullable|string',
            'problem_solving' => 'nullable|string',
            'unique_value' => 'nullable|string',
        ]);

        $ideaRequest = IdeaRequest::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status' => 'submitted',
        ]);

        return redirect()->route('ideas.show', $ideaRequest)
            ->with('success', 'Idea request submitted successfully!');
    }

    /**
     * Display the specified idea request.
     */
    public function show(IdeaRequest $idea)
    {
        $user = Auth::user();
        
        // Check access
        if ($user->isClient() && $idea->user_id !== $user->id) {
            abort(403);
        }

        $idea->load(['user', 'assignedTo', 'manager', 'comments.user']);

        return view('ideas.show', compact('idea'));
    }

    /**
     * Show AI assessment page.
     */
    public function showAiAssessment(IdeaRequest $idea)
    {
        $user = Auth::user();
        
        if ($user->isClient() && $idea->user_id !== $user->id) {
            abort(403);
        }

        return view('ideas.ai-assessment', compact('idea'));
    }

    /**
     * Process AI assessment (placeholder for external API).
     */
    public function processAiAssessment(Request $request, IdeaRequest $idea)
    {
        $validated = $request->validate([
            'ai_options' => 'required|array',
            'token_count' => 'required|integer|min:1|max:100',
        ]);

        // TODO: Integrate with actual AI API
        $idea->update([
            'status' => 'ai_assessment',
            'tokens_used' => $validated['token_count'],
            'ai_assessment_data' => [
                'visualization' => 'AI visualization data placeholder',
                'text_analysis' => 'AI text analysis placeholder',
                'processed_at' => now(),
            ],
        ]);

        return redirect()->route('ideas.show', $idea)
            ->with('info', 'AI Assessment will be available soon (External API Integration Required)');
    }

    /**
     * Show negotiation page.
     */
    public function showNegotiation(IdeaRequest $idea)
    {
        $idea->load('comments.user');
        return view('ideas.negotiation', compact('idea'));
    }

    /**
     * Add comment to negotiation.
     */
    public function addComment(Request $request, IdeaRequest $idea)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
            'suggested_price' => 'nullable|numeric|min:0',
        ]);

        IdeaRequestComment::create([
            'idea_request_id' => $idea->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'suggested_price' => $validated['suggested_price'] ?? null,
            'is_internal' => Auth::user()->isManager(),
        ]);

        if ($idea->status === 'submitted') {
            $idea->update(['status' => 'negotiation']);
        }

        return back()->with('success', 'Comment added successfully!');
    }

    /**
     * Accept quote.
     */
    public function acceptQuote(IdeaRequest $idea)
    {
        if (!$idea->isQuoted()) {
            return back()->withErrors(['error' => 'No quote available to accept.']);
        }

        $idea->update([
            'status' => 'accepted',
            'agreement_accepted_at' => now(),
        ]);

        return redirect()->route('ideas.payment', $idea)
            ->with('success', 'Quote accepted! Please upload payment confirmation.');
    }

    /**
     * Reject quote.
     */
    public function rejectQuote(Request $request, IdeaRequest $idea)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Add rejection as a comment to keep negotiation open
        IdeaRequestComment::create([
            'idea_request_id' => $idea->id,
            'user_id' => Auth::id(),
            'comment' => 'Quote rejected. Reason: ' . ($validated['reason'] ?? 'No reason provided'),
            'is_internal' => false,
        ]);

        // Return to negotiation status, NOT rejected
        $idea->update(['status' => 'negotiation']);

        return redirect()->route('ideas.negotiation', $idea)
            ->with('info', 'Quote rejected. Negotiation continues - please discuss with manager.');
    }

    /**
     * Show payment upload page.
     */
    public function showPayment(IdeaRequest $idea)
    {
        if (!$idea->isAccepted() && !$idea->isPaymentPending()) {
            abort(403);
        }

        return view('ideas.payment', compact('idea'));
    }

    /**
     * Upload payment confirmation.
     */
    public function uploadPayment(Request $request, IdeaRequest $idea)
    {
        $validated = $request->validate([
            'payment_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $path = $request->file('payment_file')->store('payments', 'private');

        $idea->update([
            'payment_file' => $path,
            'status' => 'payment_pending',
        ]);

        return redirect()->route('ideas.show', $idea)
            ->with('success', 'Payment confirmation uploaded! Waiting for manager verification.');
    }

    // MANAGER SIDE METHODS

    /**
     * Show all idea requests for managers.
     */
    public function managerIndex()
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $ideas = IdeaRequest::with(['user', 'assignedTo'])
            ->latest()
            ->paginate(15);

        return view('ideas.manager.index', compact('ideas'));
    }

    public function managerShow(IdeaRequest $idea)
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $idea->load(['user', 'assignedTo', 'comments.user']);
        $employees = User::where('role', 'employee')->get();

        return view('ideas.manager.show', compact('idea', 'employees'));
    }

    /**
     * Send quote to client.
     */
    public function sendQuote(Request $request, IdeaRequest $idea)
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $validated = $request->validate([
            'final_quote' => 'required|numeric|min:0',
            'quote_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Store quote file
        $quotePath = $request->file('quote_file')->store('quotes', 'public');

        $idea->update([
            'final_quote' => $validated['final_quote'],
            'quote_file_path' => $quotePath,
            'quote_status' => $user->isManager() ? 'approved' : 'pending_approval',
            'status' => $user->isManager() ? 'quoted' : 'negotiation',
            'quote_approved_by' => $user->isManager() ? $user->id : null,
            'quote_approved_at' => $user->isManager() ? now() : null,
            'manager_id' => $user->id,
        ]);

        // Add comment
        IdeaRequestComment::create([
            'idea_request_id' => $idea->id,
            'user_id' => $user->id,
            'comment' => ($user->isManager() ? 'Quote sent to client' : 'Quote uploaded for manager approval') . ': $' . number_format($validated['final_quote'], 2),
            'is_internal' => !$user->isManager(),
            'suggested_price' => $validated['final_quote'],
        ]);

        return back()->with('success', $user->isManager() ? 'Quote sent to client!' : 'Quote uploaded! Waiting for manager approval.');
    }

    public function approveQuote(IdeaRequest $idea)
    {
        $user = Auth::user();
        
         

        $idea->update([
            'quote_status' => 'approved',
            'quote_approved_by' => $user->id,
            'quote_approved_at' => now(),
            'status' => 'quoted',
        ]);

        IdeaRequestComment::create([
            'idea_request_id' => $idea->id,
            'user_id' => $user->id,
            'comment' => 'Quote approved and sent to client.',
            'is_internal' => true,
        ]);

        return back()->with('success', 'Quote approved and sent to client!');
    }

    /**
     * Verify payment.
     */
    public function verifyPayment(Request $request, IdeaRequest $idea)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        if ($validated['action'] === 'approve') {
            $idea->update([
                'status' => 'approved',
                'payment_verified_at' => now(),
            ]);
            
            return back()->with('success', 'Payment verified! Idea request approved.');
        } else {
            $idea->update(['status' => 'accepted']); // Back to accepted status
            
            return back()->with('error', 'Payment rejected. Client needs to re-upload.');
        }
    }

    /**
     * Assign to employee.
     */
    public function assign(Request $request, IdeaRequest $idea)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $idea->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'in_progress',
        ]);

        return back()->with('success', 'Idea request assigned successfully!');
    }

    /**
     * Close/mark as lost (manager only)
     */
    public function close(Request $request, IdeaRequest $idea)
    {
        $validated = $request->validate([
            'status' => 'required|in:rejected,cancelled',
            'reason' => 'nullable|string',
        ]);

        // Add internal note about closure
        if ($validated['reason']) {
            IdeaRequestComment::create([
                'idea_request_id' => $idea->id,
                'user_id' => Auth::id(),
                'comment' => 'Closed as ' . $validated['status'] . ': ' . $validated['reason'],
                'is_internal' => true,
            ]);
        }

        $idea->update(['status' => $validated['status']]);

        return back()->with('success', 'Request closed.');
    }

    /**
     * Convert completed idea to project
     */
    public function convertToProject(IdeaRequest $idea)
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        if (!$idea->isCompleted()) {
            return back()->withErrors(['error' => 'Only completed ideas can be converted to projects.']);
        }

        // Check if already converted
        $existingProject = \App\Models\Project::where('source_type', 'App\Models\IdeaRequest')
            ->where('source_id', $idea->id)
            ->first();

        if ($existingProject) {
            return redirect()->route('projects.manager.show', $existingProject)
                ->with('info', 'Project already exists!');
        }

        // Create project
        $project = \App\Models\Project::create([
            'client_id' => $idea->user_id,
            'title' => $idea->title,
            'description' => $idea->description,
            'scope' => "Target Market: {$idea->target_market}\n\nProblem Solving: {$idea->problem_solving}\n\nUnique Value: {$idea->unique_value}",
            'source_type' => 'App\Models\IdeaRequest',
            'source_id' => $idea->id,
            'status' => 'planning',
            'budget' => $idea->final_quote,
            'project_manager_id' => $idea->assigned_to,
        ]);

        // Add client to project_people
        \App\Models\ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $idea->user_id,
            'role' => 'client',
            'can_edit' => false,
        ]);

        // Add project manager if assigned
        if ($idea->assigned_to) {
            \App\Models\ProjectPerson::create([
                'project_id' => $project->id,
                'user_id' => $idea->assigned_to,
                'role' => 'project_manager',
                'can_edit' => true,
            ]);
        }

        return redirect()->route('projects.manager.show', $project)
            ->with('success', 'Project created from idea!');
    }
}
