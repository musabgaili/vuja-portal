<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\EngagementLog;
use App\Models\ImprovementIdea;
use App\Services\EngagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Internal "portal improvement" ideas. Any authenticated user (client, employee
 * or project manager) can submit an idea to improve the portal experience.
 * A manager reviews each idea; ONLY on manager approval does the submitter earn
 * the highest engagement (Impact Points) award.
 */
class ImprovementIdeaController extends Controller
{
    /** Engagement action awarded (once) when a manager approves an idea. */
    public const ENGAGEMENT_ACTION = 'portal_improvement_idea_approved';

    public const CATEGORIES = [
        'User Experience', 'Performance', 'New Feature', 'Automation',
        'Reporting & Analytics', 'Integration', 'Accessibility', 'Other',
    ];

    public const STATUSES = ['submitted', 'approved', 'rejected', 'implemented'];

    // ---------------- SUBMITTER SIDE (any authenticated user) ----------------

    public function index()
    {
        $ideas = ImprovementIdea::where('user_id', Auth::id())
            ->latest()->paginate(12);

        return view('improvement-ideas.index', [
            'ideas' => $ideas,
            'points' => app(EngagementService::class)->pointsFor(self::ENGAGEMENT_ACTION),
        ]);
    }

    public function create()
    {
        return view('improvement-ideas.create', [
            'categories' => self::CATEGORIES,
            'points' => app(EngagementService::class)->pointsFor(self::ENGAGEMENT_ACTION),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:120',
            'description' => 'required|string|min:20',
            'technology_used' => 'nullable|string|max:2000',
            'benefit' => 'required|string|min:10',
        ]);

        $idea = ImprovementIdea::create([
            ...$validated,
            'user_id' => Auth::id(),
            'status' => 'submitted',
        ]);

        return redirect()->route('improvement-ideas.show', $idea)
            ->with('success', __('portal.improvement_ideas.submitted'));
    }

    public function show(ImprovementIdea $improvementIdea)
    {
        $this->authorize('view', $improvementIdea);
        $improvementIdea->load(['user', 'reviewer']);

        return view('improvement-ideas.show', ['idea' => $improvementIdea]);
    }

    // ---------------- MANAGER SIDE ----------------

    public function managerIndex(Request $request)
    {
        abort_unless(Auth::user()->isManager(), 403);

        $query = ImprovementIdea::with('user');
        if ($request->filled('status') && in_array($request->input('status'), self::STATUSES, true)) {
            $query->where('status', $request->input('status'));
        }

        return view('improvement-ideas.manager.index', [
            'ideas' => $query->latest()->paginate(15),
            'statuses' => self::STATUSES,
            'points' => app(EngagementService::class)->pointsFor(self::ENGAGEMENT_ACTION),
        ]);
    }

    public function managerShow(ImprovementIdea $improvementIdea)
    {
        $this->authorize('manage', $improvementIdea);
        $improvementIdea->load(['user', 'reviewer']);

        return view('improvement-ideas.manager.show', [
            'idea' => $improvementIdea,
            'points' => app(EngagementService::class)->pointsFor(self::ENGAGEMENT_ACTION),
        ]);
    }

    public function approve(Request $request, ImprovementIdea $improvementIdea)
    {
        $this->authorize('manage', $improvementIdea);

        $validated = $request->validate(['review_notes' => 'nullable|string|max:1000']);

        // Lock the row and award inside one transaction so two concurrent
        // approvals can never double-award: the second one serialises behind the
        // first, then sees the already-approved status and skips the award.
        DB::transaction(function () use ($improvementIdea, $validated) {
            $idea = ImprovementIdea::whereKey($improvementIdea->getKey())->lockForUpdate()->first() ?? $improvementIdea;
            $wasAlreadyApproved = in_array($idea->status, ['approved', 'implemented'], true);

            $idea->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $validated['review_notes'] ?? $idea->review_notes,
            ]);

            if ($wasAlreadyApproved) {
                return; // points were granted on the first approval
            }

            // Belt-and-braces: never award twice for the same idea.
            $alreadyAwarded = EngagementLog::where('subject_type', ImprovementIdea::class)
                ->where('subject_id', $idea->id)
                ->where('action', self::ENGAGEMENT_ACTION)
                ->exists();

            if (! $alreadyAwarded && $idea->user) {
                app(EngagementService::class)->award(
                    $idea->user,
                    self::ENGAGEMENT_ACTION,
                    $idea,
                    null,
                    'Portal improvement idea approved: '.$idea->title,
                );
            }

            // Also credit the CLIENT Engagement Points program (the separate,
            // spendable loyalty currency) when a client's idea is accepted —
            // first 5 auto, beyond that held for admin review. Idempotent.
            if ($idea->user && $idea->user->isClient()) {
                app(\App\Services\Engagement\EarningEngine::class)->recordIdeaAccepted($idea->user, $idea);
            }
        });

        return back()->with('success', __('portal.improvement_ideas.approved_done'));
    }

    public function reject(Request $request, ImprovementIdea $improvementIdea)
    {
        $this->authorize('manage', $improvementIdea);

        $validated = $request->validate(['review_notes' => 'nullable|string|max:1000']);

        $improvementIdea->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? $improvementIdea->review_notes,
        ]);

        return back()->with('success', __('portal.improvement_ideas.rejected_done'));
    }
}
