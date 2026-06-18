<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\SpendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpendRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(function (Request $request, $next) {
            abort_unless($request->user()?->isInternal(), 403);

            return $next($request);
        });
    }

    /** "My requests" — everything the current user submitted. */
    public function index()
    {
        $user = Auth::user();

        return view('spend.index', [
            'requests' => SpendRequest::with(['project', 'reviewer'])
                ->where('requester_id', $user->id)->latest()->paginate(20),
            'approvalCount' => $this->pendingForApprover($user)->count(),
        ]);
    }

    /** The new-request form. */
    public function create()
    {
        return view('spend.create', [
            'projects' => $this->submittableProjects(Auth::user()),
            'categories' => config('spend.categories', ['Travel', 'Software', 'Hardware', 'Office', 'Subscriptions', 'Training', 'Other']),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'scope' => 'required|in:project,general',
            'project_id' => 'nullable|required_if:scope,project|exists:projects,id',
            'type' => 'required|in:reimbursement,purchase',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0|max:9999999999.99',
            'product_url' => 'nullable|url|max:1000',
            'quantity' => 'nullable|integer|min:1|max:100000',
            'receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // A user may only file a project request against a project they're on.
        if ($validated['scope'] === 'project') {
            abort_unless($this->submittableProjects($user)->contains('id', (int) $validated['project_id']), 403);
        } else {
            $validated['project_id'] = null;
        }

        $data = [
            'requester_id' => $user->id,
            'scope' => $validated['scope'],
            'project_id' => $validated['project_id'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'] ?? null,
            'amount' => $validated['amount'],
            'currency' => config('scope.currency', 'SAR'),
            'product_url' => $validated['type'] === 'purchase' ? ($validated['product_url'] ?? null) : null,
            'quantity' => $validated['type'] === 'purchase' ? ($validated['quantity'] ?? 1) : 1,
            'status' => 'pending',
        ];

        if ($validated['type'] === 'reimbursement' && $request->hasFile('receipt_file')) {
            $data['receipt_file'] = $request->file('receipt_file')->store('spend-receipts', 'private');
        }

        // Option A: a lone manager (no peer to approve) self-records, flagged for audit.
        if ($user->isManager() && ! $this->otherManagerExists($user)) {
            $data = array_merge($data, [
                'status' => 'approved',
                'self_recorded' => true,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
        }

        $spend = SpendRequest::create($data);

        if ($spend->isApproved()) {
            $this->settleOnApproval($spend);
        }

        return redirect()->route('spend.index')->with('success', __('portal.spend.submitted'));
    }

    public function approve(Request $request, SpendRequest $spendRequest)
    {
        $user = Auth::user();
        abort_unless($spendRequest->isApprovableBy($user), 403);

        $validated = $request->validate(['review_notes' => 'nullable|string|max:1000']);

        $spendRequest->update([
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        $this->settleOnApproval($spendRequest);

        return back()->with('success', __('portal.spend.approved'));
    }

    public function reject(Request $request, SpendRequest $spendRequest)
    {
        $user = Auth::user();
        abort_unless($spendRequest->isApprovableBy($user), 403);

        $validated = $request->validate(['review_notes' => 'nullable|string|max:1000']);

        $spendRequest->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        return back()->with('success', __('portal.spend.rejected'));
    }

    /** Mark an approved purchase as bought — records the real cost and posts the expense. */
    public function purchase(Request $request, SpendRequest $spendRequest)
    {
        $user = Auth::user();
        abort_unless($this->canFulfil($user, $spendRequest) && $spendRequest->isPurchase() && $spendRequest->isApproved(), 403);

        $validated = $request->validate([
            'actual_amount' => 'required|numeric|min:0|max:9999999999.99',
            'actual_receipt_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $receipt = $request->hasFile('actual_receipt_file')
            ? $request->file('actual_receipt_file')->store('spend-receipts', 'private')
            : null;

        $spendRequest->update([
            'status' => 'completed',
            'actual_amount' => $validated['actual_amount'],
            'actual_receipt_file' => $receipt,
            'completed_at' => now(),
        ]);

        $this->postProjectExpense($spendRequest, $spendRequest->effectiveAmount(), $receipt ?: $spendRequest->receipt_file);

        return back()->with('success', __('portal.spend.purchased'));
    }

    /** Mark an approved reimbursement as paid back to the employee. */
    public function reimburse(SpendRequest $spendRequest)
    {
        $user = Auth::user();
        abort_unless($this->canFulfil($user, $spendRequest) && $spendRequest->isReimbursement() && $spendRequest->isApproved(), 403);

        $spendRequest->update(['status' => 'completed', 'completed_at' => now()]);

        return back()->with('success', __('portal.spend.reimbursed'));
    }

    public function destroy(SpendRequest $spendRequest)
    {
        $user = Auth::user();
        // Only a still-pending request can be removed (no ledger impact yet).
        abort_unless($spendRequest->isPending() && ($spendRequest->requester_id === $user->id || $user->isManager()), 403);
        $spendRequest->delete();

        return back()->with('success', __('portal.spend.deleted'));
    }

    /** Gated download of a request's receipt (requester, fulfiller, or manager). */
    public function receipt(SpendRequest $spendRequest, string $which = 'receipt')
    {
        $user = Auth::user();
        abort_unless(
            $spendRequest->requester_id === $user->id || $user->isManager()
                || $spendRequest->reviewed_by === $user->id
                || ($spendRequest->isProject() && (int) optional($spendRequest->project)->project_manager_id === (int) $user->id),
            403
        );

        $path = $which === 'actual' ? $spendRequest->actual_receipt_file : $spendRequest->receipt_file;
        abort_unless($path && Storage::disk('private')->exists($path), 404);

        return Storage::disk('private')->download($path);
    }

    // ---- internals ----

    /** Pending requests routed to this approver. */
    private function pendingForApprover(User $user)
    {
        return SpendRequest::with(['requester', 'project'])
            ->where('status', 'pending')
            ->where('requester_id', '!=', $user->id) // never your own (no self-approval)
            ->get()
            ->filter(fn (SpendRequest $r) => $r->isApprovableBy($user))
            ->values();
    }

    public function approvals()
    {
        $user = Auth::user();

        return view('spend.approvals', [
            'pending' => $this->pendingForApprover($user),
            // Approved items this user can settle: purchases to mark bought, reimbursements to pay out.
            'awaiting' => SpendRequest::with(['requester', 'project'])
                ->where('status', 'approved')->latest('reviewed_at')->get()
                ->filter(fn (SpendRequest $r) => $this->canFulfil($user, $r))->values(),
        ]);
    }

    /** Projects the user may file a project request against. */
    private function submittableProjects(User $user)
    {
        if ($user->isManager()) {
            return Project::orderBy('title')->get(['id', 'title']);
        }

        return Project::where('project_manager_id', $user->id)
            ->orWhere('account_manager_id', $user->id)
            ->orWhereHas('projectPeople', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('title')->get(['id', 'title']);
    }

    private function otherManagerExists(User $user): bool
    {
        return User::where('role', UserRole::MANAGER)->where('id', '!=', $user->id)->exists();
    }

    private function canFulfil(User $user, SpendRequest $r): bool
    {
        // Segregation of duties: the originator may not settle their own request
        // (which records the disbursed amount) — except a lone manager who has no
        // peer and legitimately self-records (Option A).
        if ($r->requester_id === $user->id) {
            return $user->isManager() && ! $this->otherManagerExists($user);
        }

        return $user->isManager()
            || $r->reviewed_by === $user->id
            || ($r->isProject() && (int) optional($r->project)->project_manager_id === (int) $user->id);
    }

    /** Reimbursements post to the ledger on approval; purchases post on purchase. */
    private function settleOnApproval(SpendRequest $r): void
    {
        if ($r->isReimbursement()) {
            $this->postProjectExpense($r, (float) $r->amount, $r->receipt_file);
        }
        // Purchases stay "committed" (approved) until marked purchased.
    }

    /** Post a project-scope request to the ProjectExpense ledger + refresh spent. */
    private function postProjectExpense(SpendRequest $r, float $amount, ?string $receipt): void
    {
        if (! $r->isProject() || ! $r->project_id || $r->project_expense_id) {
            return; // general scope, or already posted
        }

        $expense = ProjectExpense::create([
            'project_id' => $r->project_id,
            'logged_by' => $r->requester_id,
            'title' => $r->title,
            'description' => $r->description,
            'amount' => $amount,
            'category' => $r->category,
            'expense_date' => now()->toDateString(),
            'receipt_file' => $receipt,
        ]);

        $r->forceFill(['project_expense_id' => $expense->id])->save();

        if ($r->project) {
            $r->project->update(['spent' => $r->project->expenses()->sum('amount')]);
        }
    }
}
