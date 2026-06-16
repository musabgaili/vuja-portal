<?php

namespace App\Http\Controllers;

use App\Models\ActivityCategory;
use App\Models\TeamMember;
use App\Services\Targets\AllocationService;
use App\Services\Targets\CapacityActualsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Engineer-facing capacity: the weekly allocation form + my monthly capacity
 * (utilization, allocation vs intended split, revenue, pre-sales output).
 * Self-only — always the authenticated user's own team-member record.
 */
class CapacityController extends Controller
{
    public function __construct(private AllocationService $alloc, private CapacityActualsService $actuals) {}

    private function member(): TeamMember
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal(), 403);
        $member = $user->teamMember;
        abort_unless($member && $member->is_active, 403);

        return $member;
    }

    public function index()
    {
        $member = $this->member();
        $week = $this->alloc->weekStartFor();
        $month = now()->startOfMonth();

        return view('capacity.my', [
            'member' => $member,
            'week' => $week,
            'allocation' => $this->alloc->getOrInit($member, $week),
            'categories' => ActivityCategory::active()->orderBy('sort_order')->get(),
            'available' => $this->alloc->availableHours($member, $week),
            'month' => $month,
            'utilization' => $this->actuals->utilization($member, $month),
            'utilizationTarget' => $this->actuals->utilizationTarget(),
            'allocationByCat' => $this->actuals->allocationByCategory($member, $month),
            'intended' => $this->actuals->intendedSplit($member),
            'revenue' => $this->actuals->revenue($member, $month),
            'output' => $this->actuals->output($member, $month),
            'availableMonth' => $this->actuals->availableHours($member, $month),
        ]);
    }

    public function save(Request $request)
    {
        $member = $this->member();
        $data = $request->validate([
            'week_start' => 'required|date',
            'allocations' => 'required|array',
            'allocations.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $week = Carbon::parse($data['week_start'])->startOfWeek(Carbon::MONDAY);
        $lines = collect($data['allocations'])
            ->filter(fn ($v) => $v !== null && (float) $v > 0)
            ->map(fn ($v) => (float) $v)->all();

        try {
            $this->alloc->save($member, $week, $lines, $request->boolean('submit'));

            return back()->with('success', $request->boolean('submit') ? __('targets.cap.submitted') : __('targets.cap.saved'));
        } catch (\Throwable $e) {
            return back()->withErrors(['allocation' => $e->getMessage()]);
        }
    }
}
