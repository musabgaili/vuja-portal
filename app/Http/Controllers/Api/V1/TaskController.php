<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\Models\StaffTask;
use App\Services\Api\MyTasksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private MyTasksService $tasks) {}

    /** Unified inbox: staff tasks + project tasks assigned to the user. */
    public function myTasks(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->integer('page', 1));
        $perPage = min(100, max(1, (int) $request->integer('per_page', 20)));
        $status = $request->query('status');

        return response()->json(
            $this->tasks->list($request->user(), is_string($status) ? $status : null, $page, $perPage)
        );
    }

    public function updateStaffTask(Request $request, StaffTask $staffTask): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', StaffTask::STATUSES),
        ]);

        $task = $this->tasks->updateStaffTaskStatus($staffTask, $request->user(), $data['status']);

        return response()->json(['task' => $task]);
    }

    public function updateProjectTask(Request $request, ProjectTask $projectTask): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:todo,in_progress,review,completed,blocked,cancelled',
        ]);

        $task = $this->tasks->updateProjectTaskStatus($projectTask, $request->user(), $data['status']);

        return response()->json(['task' => $task]);
    }
}
