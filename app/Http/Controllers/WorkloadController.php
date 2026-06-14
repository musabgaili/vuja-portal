<?php

namespace App\Http\Controllers;

use App\Models\StaffTask;
use App\Models\User;
use App\Services\WorkloadService;
use Illuminate\Support\Facades\Auth;

class WorkloadController extends Controller
{
    private const CAP_COLORS = [
        'idle' => '#94a3b8', 'light' => '#16a34a', 'moderate' => '#0F969C',
        'busy' => '#d97706', 'overloaded' => '#dc2626',
    ];

    public function index(WorkloadService $workload)
    {
        abort_unless(Auth::user()->isManager(), 403);

        return view('workload.index', $workload->grid());
    }

    /** Manager drill-down: one employee's projects + tasks, with inline management. */
    public function show(User $user, WorkloadService $workload)
    {
        abort_unless(Auth::user()->isManager(), 403);
        abort_unless($user->isInternal(), 404);

        return view('workload.show', array_merge($workload->employeeDetail($user), [
            'employee' => $user,
            'categories' => StaffTask::CATEGORIES,
            'priorities' => StaffTask::PRIORITIES,
            'capColors' => self::CAP_COLORS,
        ]));
    }
}
