<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Quote;
use App\Models\QuoteComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    // ---- Internal ----

    public function index()
    {
        abort_unless(Auth::user()->isInternal(), 403);

        return view('crm.quotes.index', [
            'quotes' => Quote::with(['client', 'opportunity', 'creator'])->latest()->paginate(20),
        ]);
    }

    public function show(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);

        return view('crm.quotes.show', [
            'quote' => $quote->load(['items', 'client', 'opportunity', 'company', 'project', 'approver', 'comments.user']),
            'canApprove' => $this->isApprover(),
            'clients' => User::where('role', \App\Enums\UserRole::CLIENT)->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /** Link a client account to a quote so it can be sent to / accepted for them. */
    public function assignClient(Request $request, Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        $validated = $request->validate([
            'client_id' => ['required', \Illuminate\Validation\Rule::exists('users', 'id')->where('role', \App\Enums\UserRole::CLIENT->value)],
        ]);
        $quote->update(['client_id' => $validated['client_id']]);

        return back()->with('success', __('portal.quote.client_assigned'));
    }

    /** Only a draft/changes_requested quote may go to the client AFTER approval. */
    public function send(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        if ($quote->status !== 'approved') {
            return back()->withErrors(['send' => __('portal.quote.must_be_approved')]);
        }
        // Can't send a quote to "the client" if no client is linked yet.
        if (! $quote->client_id) {
            return back()->withErrors(['send' => __('portal.quote.no_client_link_warning')]);
        }
        $firstSend = $quote->sent_at === null;
        $quote->update(['status' => 'sent', 'sent_at' => $quote->sent_at ?? now()]);

        // "Quote produced" = a quote sent to the client. Credit the author, gated
        // behind their monthly quotes target. Only on first send (idempotent).
        if ($firstSend && $quote->created_by) {
            if ($creator = User::find($quote->created_by)) {
                app(\App\Services\Targets\TargetPointsGate::class)
                    ->awardIfEarned($creator, 'quote_produced', $quote, 'Quote sent: '.$quote->title);
            }
        }

        return back()->with('success', __('portal.quote.marked_sent'));
    }

    // ---- Internal approval layer (manager / project manager) ----

    /** Creator/staff submits a draft for manager approval. */
    public function submitForApproval(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        if (! $quote->isEditable()) {
            return back()->withErrors(['approval' => __('portal.quote.not_submittable')]);
        }
        $quote->update(['status' => 'pending_approval']);
        $this->logComment($quote, 'submitted', __('portal.quote.log_submitted'));

        return back()->with('success', __('portal.quote.submitted_for_approval'));
    }

    public function approve(Quote $quote)
    {
        abort_unless($this->isApprover(), 403);
        if ($quote->status !== 'pending_approval') {
            return back()->withErrors(['approval' => __('portal.quote.not_pending')]);
        }
        $quote->update(['status' => 'approved', 'approved_by' => Auth::id(), 'approved_at' => now()]);
        $this->logComment($quote, 'approval', __('portal.quote.log_approved'));

        return back()->with('success', __('portal.quote.approved'));
    }

    public function reject(Request $request, Quote $quote)
    {
        abort_unless($this->isApprover(), 403);
        $validated = $request->validate(['comment' => 'required|string|max:2000']);
        $quote->update(['status' => 'rejected', 'reject_reason' => $validated['comment']]);
        $this->logComment($quote, 'rejection', $validated['comment']);

        return back()->with('success', __('portal.quote.rejected'));
    }

    /** Send the quote back to the creator for changes, with a required comment. */
    public function requestChanges(Request $request, Quote $quote)
    {
        abort_unless($this->isApprover(), 403);
        $validated = $request->validate(['comment' => 'required|string|max:2000']);
        $quote->update(['status' => 'changes_requested']);
        $this->logComment($quote, 'changes', $validated['comment']);

        return back()->with('success', __('portal.quote.changes_requested'));
    }

    public function addComment(Request $request, Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        $validated = $request->validate(['comment' => 'required|string|max:2000']);
        $this->logComment($quote, 'note', $validated['comment']);

        return back()->with('success', __('portal.quote.comment_added'));
    }

    private function isApprover(): bool
    {
        $u = Auth::user();

        return $u->isManager() || $u->isProjectManager();
    }

    private function logComment(Quote $quote, string $type, string $comment): void
    {
        QuoteComment::create([
            'quote_id' => $quote->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'comment' => $comment,
        ]);
    }

    /** Internal accept-on-behalf (e.g. verbal/phone acceptance). */
    public function acceptInternal(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        $project = $this->acceptAndConvert($quote, Auth::user()->name.' (internal)', request()->ip());

        return redirect()->route('projects.manager.show', $project)
            ->with('success', 'Quote accepted — order/project created.');
    }

    public function destroy(Quote $quote)
    {
        abort_unless(Auth::user()->isInternal(), 403);
        $quote->delete();

        return redirect()->route('quotes.index')->with('success', 'Quote deleted.');
    }

    // ---- Client portal ----

    public function clientIndex()
    {
        $user = Auth::user();
        abort_unless($user->canUseClientProjectPortal(), 403);

        return view('crm.quotes.client-index', [
            'quotes' => Quote::where('client_id', $user->id)->whereIn('status', ['sent', 'accepted'])->latest()->get(),
        ]);
    }

    public function clientShow(Quote $quote)
    {
        $this->guardClient($quote);

        return view('crm.quotes.client-show', ['quote' => $quote->load('items')]);
    }

    public function clientAccept(Request $request, Quote $quote)
    {
        $this->guardClient($quote);
        if ($quote->status !== 'sent') {
            return back()->withErrors(['accept' => 'This quote is not awaiting your acceptance.']);
        }
        $validated = $request->validate(['signature' => 'required|string|max:120']);
        $this->acceptAndConvert($quote, $validated['signature'], $request->ip());

        return redirect()->route('quotes.client.show', $quote)->with('success', 'Quote accepted — thank you! Your project is now underway.');
    }

    public function clientReject(Request $request, Quote $quote)
    {
        $this->guardClient($quote);
        $validated = $request->validate(['reject_reason' => 'nullable|string|max:255']);
        $quote->update(['status' => 'rejected', 'reject_reason' => $validated['reject_reason'] ?? null]);

        // If the client had applied a discount voucher to this quote, release it and
        // refund the points — declining must never strand their loyalty currency.
        app(\App\Services\Engagement\RedemptionService::class)->releaseForQuote($quote);

        return back()->with('success', 'Quote declined.');
    }

    private function guardClient(Quote $quote): void
    {
        $user = Auth::user();
        abort_unless($user->canUseClientProjectPortal() && $quote->client_id === $user->id, 403);
    }

    /** Accepting a quote creates the order (a Project) and wins any linked opportunity. */
    private function acceptAndConvert(Quote $quote, ?string $signature, ?string $ip): Project
    {
        if ($quote->isAccepted() && $quote->project) {
            return $quote->project;
        }

        // One deal = one project. If this quote's opportunity was already converted
        // to a project (e.g. the manager clicked "Win & convert"), reuse THAT project
        // instead of creating a duplicate that double-books budget/revenue.
        $existingProject = $quote->opportunity?->converted_project_id
            ? Project::find($quote->opportunity->converted_project_id)
            : null;

        $project = DB::transaction(function () use ($quote, $signature, $ip, $existingProject) {
            $project = $existingProject ?: Project::create([
                'title' => $quote->title,
                'description' => $quote->scope ? \Illuminate\Support\Str::limit(strip_tags($quote->scope), 4000) : $quote->title,
                'client_id' => $quote->client_id,
                'budget' => $quote->total_client,
                'status' => 'awarded',
                'project_manager_id' => $quote->opportunity?->owner_id ?? $quote->created_by,
                'source_type' => Quote::class,
                'source_id' => $quote->id,
            ]);

            $quote->update([
                'status' => 'accepted',
                'accepted_signature' => $signature,
                'accepted_at' => now(),
                'accepted_ip' => $ip,
                'project_id' => $project->id,
            ]);

            if ($quote->opportunity && $quote->opportunity->isOpen()) {
                $quote->opportunity->update([
                    'stage' => 'won', 'won_at' => now(), 'probability' => 100, 'converted_project_id' => $project->id,
                ]);
            }

            return $project;
        });

        // "Project won" — credit everyone who owns the win (quote author, opportunity
        // owner, project PM), gated behind each one's monthly wins target. Deduped
        // per person by the gate's distinct-project count.
        $this->creditProjectWin($quote, $project);

        return $project;
    }

    private function creditProjectWin(Quote $quote, Project $project): void
    {
        $gate = app(\App\Services\Targets\TargetPointsGate::class);
        $userIds = collect([
            $quote->created_by,
            $quote->opportunity?->owner_id,
            $project->project_manager_id,
        ])->filter()->unique();

        foreach ($userIds as $uid) {
            if ($u = User::find($uid)) {
                $gate->awardIfEarned($u, 'project_won', $project, 'Project won: '.$project->title);
            }
        }
    }
}
