<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointGrantRequest;
use App\Models\User;
use App\Services\Engagement\GrantApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Discretionary grants (spec §13): a Project Manager suggests; a Manager
 * approves. Two-person integrity is enforced in the service and the gates.
 */
class EngagementGrantController extends Controller
{
    public function __construct(private GrantApprovalService $grants) {}

    public function index()
    {
        // Only PMs (who suggest) and managers (who approve) may see the queue +
        // client roster — not every internal employee.
        abort_unless(Gate::allows('suggest-grant'), 403);

        $requests = PointGrantRequest::with('client', 'suggestedBy', 'approvedBy')->latest()->paginate(20);
        $clients = User::where('role', 'client')->orderBy('name')->get(['id', 'name', 'email']);
        $canApprove = Gate::allows('manage-engagement');
        $canSuggest = Gate::allows('suggest-grant');

        return view('engagement.admin.grants', compact('requests', 'clients', 'canApprove', 'canSuggest'));
    }

    public function store(Request $request)
    {
        abort_unless(Gate::allows('suggest-grant'), 403);
        $data = $request->validate([
            'client_id' => 'required|integer|exists:users,id',
            'points' => 'required|integer|min:1|max:100000',
            'reason' => 'required|string|max:500',
        ]);
        $client = User::findOrFail($data['client_id']);
        abort_unless($client->isClient(), 422);

        $this->grants->suggest($client, $data['points'], $data['reason'], Auth::user());

        return back()->with('success', __('engagement.admin.grant_suggested'));
    }

    public function approve(PointGrantRequest $grant)
    {
        abort_unless(Gate::allows('manage-engagement'), 403);

        try {
            $this->grants->approve($grant, Auth::user());

            return back()->with('success', __('engagement.admin.grant_approved'));
        } catch (\Throwable $e) {
            return back()->withErrors(['grant' => $e->getMessage()]);
        }
    }

    public function reject(PointGrantRequest $grant)
    {
        abort_unless(Gate::allows('manage-engagement'), 403);
        $this->grants->reject($grant, Auth::user());

        return back()->with('success', __('engagement.admin.grant_rejected'));
    }
}
