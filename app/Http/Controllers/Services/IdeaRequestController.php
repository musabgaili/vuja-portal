<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\IdeaRequest;
use App\Models\IdeaRequestComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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
        $this->authorize('view', $idea);

        $idea->load(['user', 'assignedTo', 'manager', 'comments.user']);

        return view('ideas.show', compact('idea'));
    }

    /**
     * Show AI assessment page.
     */
    public function showAiAssessment(IdeaRequest $idea)
    {
        $this->authorize('view', $idea);

        return view('ideas.ai-assessment', compact('idea'));
    }

    /**
     * Process AI assessment (placeholder for external API).
     */
    public function processAiAssessment(Request $request, IdeaRequest $idea)
    {
        $this->authorize('update', $idea);

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
        $this->authorize('view', $idea);

        $idea->load('comments.user');

        return view('ideas.negotiation', compact('idea'));
    }

    /**
     * Add comment to negotiation.
     */
    public function addComment(Request $request, IdeaRequest $idea)
    {
        $this->authorize('update', $idea);

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
        $this->authorize('update', $idea);

        if (! $idea->isQuoted()) {
            return back()->withErrors(['error' => 'No quote available to accept.']);
        }

        $idea->update([
            'status' => 'accepted',
            'agreement_accepted_at' => now(),
        ]);

        $this->notifyInternalsOfQuoteDecision($idea, new \App\Mail\QuoteApproved($idea));

        return redirect()->route('ideas.payment', $idea)
            ->with('success', 'Quote accepted! Please upload payment confirmation.');
    }

    /**
     * Reject quote.
     */
    public function rejectQuote(Request $request, IdeaRequest $idea)
    {
        $this->authorize('update', $idea);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Add rejection as a comment to keep negotiation open
        IdeaRequestComment::create([
            'idea_request_id' => $idea->id,
            'user_id' => Auth::id(),
            'comment' => 'Quote rejected. Reason: '.($validated['reason'] ?? 'No reason provided'),
            'is_internal' => false,
        ]);

        // Return to negotiation status, NOT rejected
        $idea->update(['status' => 'negotiation']);

        $this->notifyInternalsOfQuoteDecision($idea, new \App\Mail\QuoteRejected($idea, $validated['reason'] ?? null));

        return redirect()->route('ideas.negotiation', $idea)
            ->with('info', 'Quote rejected. Negotiation continues - please discuss with manager.');
    }

    /**
     * Show payment upload page.
     */
    public function showPayment(IdeaRequest $idea)
    {
        $this->authorize('view', $idea);

        if (! $idea->isAccepted() && ! $idea->isPaymentPending()) {
            abort(403);
        }

        return view('ideas.payment', compact('idea'));
    }

    /**
     * Upload payment confirmation.
     */
    public function uploadPayment(Request $request, IdeaRequest $idea)
    {
        $this->authorize('update', $idea);

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

        if (! $user->isInternal()) {
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

        $this->authorize('manage', $idea);

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

        $this->authorize('manage', $idea);

        // The quote can be sent two ways: the detail page (price + uploaded
        // quote document) or the quick "send quote" modal on the list page
        // (price + agreement terms, no file). Both must work, so the file is
        // optional and we keep any previously attached document.
        $validated = $request->validate([
            'final_quote' => 'required|numeric|min:0',
            'quote_file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'agreement_terms' => 'nullable|string',
        ]);

        // Store quote file when one was attached, otherwise keep the existing one.
        $quotePath = $idea->quote_file_path;
        if ($request->hasFile('quote_file')) {
            $quotePath = $request->file('quote_file')->store('quotes', 'public');
        }

        $idea->update([
            'final_quote' => $validated['final_quote'],
            'quote_file_path' => $quotePath,
            'agreement_terms' => $validated['agreement_terms'] ?? $idea->agreement_terms,
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
            'comment' => ($user->isManager() ? 'Quote sent to client' : 'Quote uploaded for manager approval').': $'.number_format($validated['final_quote'], 2),
            'is_internal' => ! $user->isManager(),
            'suggested_price' => $validated['final_quote'],
        ]);

        return back()->with('success', $user->isManager() ? 'Quote sent to client!' : 'Quote uploaded! Waiting for manager approval.');
    }

    public function approveQuote(Request $request, IdeaRequest $idea)
    {
        $user = Auth::user();

        $this->authorize('manage', $idea);

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

        return $this->respondToManagerAction($request, 'Quote approved and sent to client!');
    }

    /**
     * Verify payment.
     */
    public function verifyPayment(Request $request, IdeaRequest $idea)
    {
        $user = Auth::user();

        $this->authorize('manage', $idea);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
        ]);

        if ($validated['action'] === 'approve') {
            $idea->update([
                'status' => 'approved',
                'payment_verified_at' => now(),
            ]);

            return $this->respondToManagerAction($request, 'Payment verified! Idea request approved.');
        } else {
            $idea->update(['status' => 'accepted']); // Back to accepted status

            return $this->respondToManagerAction($request, 'Payment rejected. Client needs to re-upload.', false, 422);
        }
    }

    /**
     * Assign to employee.
     */
    public function assign(Request $request, IdeaRequest $idea)
    {
        $user = Auth::user();

        $this->authorize('manage', $idea);

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
        $this->authorize('manage', $idea);

        $validated = $request->validate([
            'status' => 'required|in:rejected,cancelled',
            'reason' => 'nullable|string',
        ]);

        // Add internal note about closure
        if ($validated['reason']) {
            IdeaRequestComment::create([
                'idea_request_id' => $idea->id,
                'user_id' => Auth::id(),
                'comment' => 'Closed as '.$validated['status'].': '.$validated['reason'],
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
        $this->authorize('manage', $idea);

        if (! $idea->isCompleted()) {
            return back()->withErrors(['error' => 'Only completed ideas can be converted to projects.']);
        }

        $already = $idea->isConvertedToProject();

        $project = app(\App\Services\ServiceProjectConverter::class)->convert($idea, [
            'title' => $idea->title,
            'description' => $idea->description,
            'scope' => "Target Market: {$idea->target_market}\n\nProblem Solving: {$idea->problem_solving}\n\nUnique Value: {$idea->unique_value}",
            'client_id' => $idea->user_id,
            'budget' => $idea->final_quote,
            'project_manager_id' => $idea->assigned_to,
            'source_label' => 'Idea',
        ]);

        return redirect()->route('projects.manager.show', $project)
            ->with($already ? 'info' : 'success', $already ? 'Project already exists!' : 'Project created from idea — added to the sales funnel.');
    }

    /**
     * Send a quote-decision email to the assigned employee, the quote-approving manager,
     * and any user with the manager role so leadership stays in the loop.
     */
    protected function notifyInternalsOfQuoteDecision(IdeaRequest $idea, $mailable): void
    {
        $recipients = collect();

        if ($idea->assignedTo) {
            $recipients->push($idea->assignedTo->email);
        }

        if ($idea->manager_id) {
            $manager = User::find($idea->manager_id);
            if ($manager) {
                $recipients->push($manager->email);
            }
        }

        $managers = User::where('type', 'internal')
            ->whereHas('roles', fn ($q) => $q->where('name', 'manager'))
            ->pluck('email');

        $recipients = $recipients->merge($managers)->filter()->unique()->values();

        foreach ($recipients as $email) {
            Mail::to($email)->send($mailable);
        }
    }

    protected function respondToManagerAction(Request $request, string $message, bool $success = true, int $status = 200)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $status);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
