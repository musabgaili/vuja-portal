<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Project;
use App\Models\StaffTask;
use App\Models\User;
use App\Services\EngagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Direct staff tasks — a manager assigns a discrete piece of work (project,
 * sales, pre-sale or management) to an internal user. Completing one awards
 * Impact Points at the higher staff_task_* rate for its category.
 */
class StaffTaskController extends Controller
{
    public function __construct(private EngagementService $engagement)
    {
    }

    /** Manager sees every task; an employee sees only what's assigned to them. */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $isManager = $user->isManager();

        $query = StaffTask::with(['assignee', 'assigner', 'project', 'opportunity'])
            ->latest();

        if (! $isManager) {
            $query->where('assigned_to', $user->id);
        } elseif ($request->filled('assignee')) {
            $query->where('assigned_to', (int) $request->input('assignee'));
        }

        if (in_array($request->input('category'), StaffTask::CATEGORIES, true)) {
            $query->where('category', $request->input('category'));
        }
        if (in_array($request->input('status'), StaffTask::STATUSES, true)) {
            $query->where('status', $request->input('status'));
        }

        $tasks = $query->get();

        return view('staff-tasks.index', [
            'tasks' => $tasks,
            'isManager' => $isManager,
            'categories' => StaffTask::CATEGORIES,
            'statuses' => StaffTask::STATUSES,
            'rates' => $this->engagement->actionPoints(),
            'staff' => $isManager
                ? User::where('type', 'internal')->orderBy('name')->get()
                : collect(),
            'filters' => [
                'assignee' => $request->input('assignee'),
                'category' => $request->input('category'),
                'status' => $request->input('status'),
            ],
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        abort_unless($user->isManager(), 403);

        return view('staff-tasks.create', [
            'staff' => User::where('type', 'internal')->orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(['id', 'title']),
            'opportunities' => Opportunity::orderBy('name')->get(['id', 'name']),
            'categories' => StaffTask::CATEGORIES,
            'priorities' => StaffTask::PRIORITIES,
            'rates' => $this->engagement->actionPoints(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isManager(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'category' => 'required|in:'.implode(',', StaffTask::CATEGORIES),
            'priority' => 'required|in:'.implode(',', StaffTask::PRIORITIES),
            'assigned_to' => 'required|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'opportunity_id' => 'nullable|exists:opportunities,id',
            'due_date' => 'nullable|date',
        ]);

        $validated['assigned_by'] = $user->id;
        $validated['status'] = 'open';

        // A project task should not also dangle an opportunity and vice versa,
        // but we don't force a link — both stay optional.
        StaffTask::create($validated);

        return redirect()->route('staff-tasks.index')
            ->with('success', __('portal.staff_tasks.created'));
    }

    /** Assignee or a manager advances the task. Completing it awards IP once. */
    public function updateStatus(Request $request, StaffTask $staffTask)
    {
        $user = Auth::user();
        abort_unless($user->isManager() || $staffTask->assigned_to === $user->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', StaffTask::STATUSES),
        ]);

        $newStatus = $validated['status'];
        $staffTask->status = $newStatus;

        if ($newStatus === 'done') {
            $staffTask->completed_at = $staffTask->completed_at ?? now();

            // Award the category's IP rate to the assignee, exactly once.
            if (! $staffTask->points_awarded) {
                $this->engagement->award(
                    $staffTask->assignee,
                    $staffTask->engagementAction(),
                    $staffTask,
                    null,
                    'Direct task completed: '.$staffTask->title,
                );
                $staffTask->points_awarded = true;
            }
        } elseif ($newStatus !== 'done') {
            $staffTask->completed_at = null;
        }

        $staffTask->save();

        return back()->with('success', __('portal.staff_tasks.status_updated'));
    }

    public function destroy(StaffTask $staffTask)
    {
        abort_unless(Auth::user()->isManager(), 403);

        $staffTask->delete();

        return redirect()->route('staff-tasks.index')
            ->with('success', __('portal.staff_tasks.deleted'));
    }
}
