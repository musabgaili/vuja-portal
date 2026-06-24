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
            'requests' => SpendRequest::with(['project', 'reviewer', 'items', 'receipts'])
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
        $validated = $this->validatePayload($request);

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
            'category' => $validated['scope'] === 'general' ? ($validated['category'] ?? null) : null,
            'amount' => $this->itemsTotal($validated['items']),
            'currency' => config('scope.currency', 'SAR'),
            'status' => 'pending',
        ];

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
        $this->syncItems($spend, $validated['items']);
        $this->storeUploadedReceipts($spend, $request);

        if ($spend->isApproved()) {
            $spend->load('receipts');
            $this->settleOnApproval($spend);
        }

        return redirect()->route('spend.show', $spend)->with('success', __('portal.spend.submitted'));
    }

    public function edit(SpendRequest $spendRequest)
    {
        $user = Auth::user();
        abort_unless($this->canEdit($user, $spendRequest), 403);
        $spendRequest->load(['items', 'receipts']);

        return view('spend.edit', [
            'spend' => $spendRequest,
            'projects' => $this->submittableProjects($user),
            'categories' => config('spend.categories', ['Travel', 'Software', 'Hardware', 'Office', 'Subscriptions', 'Training', 'Other']),
        ]);
    }

    public function update(Request $request, SpendRequest $spendRequest)
    {
        $user = Auth::user();
        abort_unless($this->canEdit($user, $spendRequest), 403);
        $validated = $this->validatePayload($request);

        if ($validated['scope'] === 'project') {
            abort_unless($this->submittableProjects($user)->contains('id', (int) $validated['project_id']), 403);
        } else {
            $validated['project_id'] = null;
        }

        $spendRequest->update([
            'scope' => $validated['scope'],
            'project_id' => $validated['project_id'],
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['scope'] === 'general' ? ($validated['category'] ?? null) : null,
            'amount' => $this->itemsTotal($validated['items']),
        ]);

        $this->syncItems($spendRequest, $validated['items']);

        // Remove receipts the user unticked.
        foreach ((array) $request->input('remove_files', []) as $fid) {
            $f = $spendRequest->files()->whereKey($fid)->first();
            if ($f) {
                Storage::disk('private')->delete($f->path);
                $f->delete();
            }
        }
        $this->storeUploadedReceipts($spendRequest, $request);

        return redirect()->route('spend.show', $spendRequest)->with('success', __('portal.spend.updated'));
    }

    /** Full detail of a single request — items, links, receipts, history, actions. */
    public function show(SpendRequest $spendRequest)
    {
        $user = Auth::user();
        $spendRequest->load(['items', 'files.uploader', 'requester', 'project', 'reviewer']);
        abort_unless($this->canView($user, $spendRequest), 403);

        return view('spend.show', [
            'spend' => $spendRequest,
            'canApprove' => $spendRequest->isApprovableBy($user),
            'canFulfil' => $this->canFulfil($user, $spendRequest),
            'canEdit' => $this->canEdit($user, $spendRequest),
        ]);
    }

    /** Manager / PM hub: every request, all statuses, filterable, with totals. */
    public function manage(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isManager() || $user->isProjectManager(), 403);

        $base = SpendRequest::with(['requester', 'project', 'reviewer'])->latest();

        // A PM only sees requests on projects they manage, plus their own.
        if (! $user->isManager()) {
            $pmProjectIds = Project::where('project_manager_id', $user->id)->pluck('id');
            $base->where(fn ($w) => $w->whereIn('project_id', $pmProjectIds)->orWhere('requester_id', $user->id));
        }

        foreach ([
            'status' => ['pending', 'approved', 'completed', 'rejected'],
            'type' => ['reimbursement', 'purchase'],
            'scope' => ['project', 'general'],
        ] as $field => $allowed) {
            if (in_array($request->query($field), $allowed, true)) {
                $base->where($field, $request->query($field));
            }
        }
        if ($pid = (int) $request->query('project_id')) {
            $base->where('project_id', $pid);
        }
        if ($rid = (int) $request->query('requester_id')) {
            $base->where('requester_id', $rid);
        }
        if ($term = trim((string) $request->query('q', ''))) {
            $base->where('title', 'like', '%'.$term.'%');
        }
        if ($from = $request->query('from')) {
            try {
                $base->whereDate('created_at', '>=', \Carbon\Carbon::parse($from)->toDateString());
            } catch (\Throwable $e) {
            }
        }
        if ($to = $request->query('to')) {
            try {
                $base->whereDate('created_at', '<=', \Carbon\Carbon::parse($to)->toDateString());
            } catch (\Throwable $e) {
            }
        }

        // Totals across the filtered set (before pagination consumes the builder).
        $summary = [
            'count' => (clone $base)->count(),
            'pending' => (float) (clone $base)->where('status', 'pending')->sum('amount'),
            'approved' => (float) (clone $base)->where('status', 'approved')->sum('amount'),
            'completed' => (float) (clone $base)->where('status', 'completed')->whereNotNull('actual_amount')->sum('actual_amount')
                + (float) (clone $base)->where('status', 'completed')->whereNull('actual_amount')->sum('amount'),
        ];

        return view('spend.manage', [
            'requests' => $base->paginate(25)->withQueryString(),
            'summary' => $summary,
            'projects' => Project::orderBy('title')->get(['id', 'title']),
            'requesters' => User::where('type', 'internal')->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['status', 'type', 'scope', 'project_id', 'requester_id', 'q', 'from', 'to']),
            'currency' => config('scope.currency', 'SAR'),
        ]);
    }

    /** Gated download of any receipt/attachment row (new files table). */
    public function fileDownload(\App\Models\SpendRequestFile $file)
    {
        $user = Auth::user();
        $r = $file->spendRequest;
        abort_unless($r && $this->canView($user, $r), 403);
        abort_unless(Storage::disk('private')->exists($file->path), 404);

        return Storage::disk('private')->download($file->path, $file->displayName());
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
            'actual_receipts' => 'nullable|array|max:10',
            'actual_receipts.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $this->storeUploadedReceipts($spendRequest, $request, 'actual_receipts', 'actual_receipt');

        $spendRequest->update([
            'status' => 'completed',
            'actual_amount' => $validated['actual_amount'],
            'completed_at' => now(),
        ]);

        $spendRequest->load(['actualReceipts', 'receipts']);
        $receiptPath = $spendRequest->actualReceipts->first()->path ?? $spendRequest->firstReceiptPath();
        $this->postProjectExpense($spendRequest, $spendRequest->effectiveAmount(), $receiptPath);

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
        return SpendRequest::with(['requester', 'project', 'items', 'receipts'])
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
            'awaiting' => SpendRequest::with(['requester', 'project', 'items'])
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
            $this->postProjectExpense($r, (float) $r->amount, $r->firstReceiptPath());
        }
        // Purchases stay "committed" (approved) until marked purchased.
    }

    /** Shared validation for create + update (header + line items + receipts). */
    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'scope' => 'required|in:project,general',
            'project_id' => 'nullable|required_if:scope,project|exists:projects,id',
            'type' => 'required|in:reimbursement,purchase',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'items' => 'required|array|min:1|max:50',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'nullable|integer|min:1|max:100000',
            'items.*.unit_amount' => 'required|numeric|min:0|max:9999999999.99',
            'items.*.product_url' => 'nullable|url|max:1000',
            'receipts' => 'nullable|array|max:10',
            'receipts.*' => 'file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);
    }

    /** Sum of (quantity × unit amount) across the submitted line items. */
    private function itemsTotal(array $items): float
    {
        $sum = 0.0;
        foreach ($items as $it) {
            $sum += max(1, (int) ($it['quantity'] ?? 1)) * (float) ($it['unit_amount'] ?? 0);
        }

        return round($sum, 2);
    }

    /** Replace a request's line items with the submitted set. */
    private function syncItems(SpendRequest $spend, array $items): void
    {
        $spend->items()->delete();
        $order = 0;
        foreach ($items as $it) {
            if (trim((string) ($it['description'] ?? '')) === '') {
                continue;
            }
            $spend->items()->create([
                'description' => $it['description'],
                'quantity' => max(1, (int) ($it['quantity'] ?? 1)),
                'unit_amount' => (float) ($it['unit_amount'] ?? 0),
                'product_url' => $it['product_url'] ?? null,
                'sort_order' => $order++,
            ]);
        }
    }

    /** Store uploaded receipt/attachment files onto the request (private disk). */
    private function storeUploadedReceipts(SpendRequest $spend, Request $request, string $field = 'receipts', string $kind = 'receipt'): void
    {
        foreach ((array) $request->file($field, []) as $file) {
            if (! $file) {
                continue;
            }
            $spend->files()->create([
                'kind' => $kind,
                'path' => $file->store('spend-receipts', 'private'),
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private function canEdit(User $user, SpendRequest $r): bool
    {
        return $r->isPending() && ($r->requester_id === $user->id || $user->isManager());
    }

    /** Who may open a request's detail page. */
    private function canView(User $user, SpendRequest $r): bool
    {
        return $r->requester_id === $user->id
            || $user->isManager()
            || (int) $r->reviewed_by === (int) $user->id
            || ($r->isProject() && (int) optional($r->project)->project_manager_id === (int) $user->id)
            || $r->isApprovableBy($user)
            || $this->canFulfil($user, $r);
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
