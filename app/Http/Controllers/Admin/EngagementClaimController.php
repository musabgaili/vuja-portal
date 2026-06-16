<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EngagementClaim;
use App\Services\Engagement\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

/** Claims review queue (spec §11, §14). Manager-only. */
class EngagementClaimController extends Controller
{
    public function __construct(private ClaimService $claims) {}

    private function guard(): void
    {
        abort_unless(Gate::allows('manage-engagement'), 403);
    }

    public function index(Request $request)
    {
        $this->guard();
        $status = $request->get('status', 'submitted');

        $claims = EngagementClaim::with('account.client', 'reviewer')
            ->when(in_array($status, ['submitted', 'approved', 'rejected'], true), fn ($q) => $q->where('status', $status))
            ->latest()->paginate(20)->withQueryString();

        return view('engagement.admin.claims', compact('claims', 'status'));
    }

    public function approve(Request $request, EngagementClaim $claim)
    {
        $this->guard();
        $request->validate(['review_notes' => 'nullable|string|max:1000']);

        try {
            $this->claims->approve($claim, Auth::id(), $request->input('review_notes'));

            return back()->with('success', __('engagement.admin.claim_approved'));
        } catch (\Throwable $e) {
            return back()->withErrors(['claim' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, EngagementClaim $claim)
    {
        $this->guard();
        $request->validate(['review_notes' => 'nullable|string|max:1000']);
        $this->claims->reject($claim, Auth::id(), $request->input('review_notes'));

        return back()->with('success', __('engagement.admin.claim_rejected'));
    }

    public function screenshot(EngagementClaim $claim)
    {
        $this->guard();
        abort_unless($claim->screenshot_path && Storage::disk('private')->exists($claim->screenshot_path), 404);

        return Storage::disk('private')->download($claim->screenshot_path);
    }
}
