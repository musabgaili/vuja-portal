<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Actions\Projects\UpdateProjectProgressAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function getData(ProjectTask $task)
    {
        $user = Auth::user();
        
        if (!$task->project->canUserView($user)) {
            abort(403);
        }

        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'milestone_id' => $task->milestone_id,
            'assigned_to' => $task->assigned_to,
            'due_date' => $task->due_date?->format('Y-m-d'),
            'actual_hours' => $task->actual_hours,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        if (!$project->canUserManageTasks($user)) {
            abort(403, 'You do not have permission to create tasks.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'milestone_id' => 'nullable|exists:project_milestones,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|integer|min:0',
        ]);

        $validated['project_id'] = $project->id;
        $validated['created_by'] = $user->id;

        $task = ProjectTask::create($validated);

        // Update milestone progress if task is linked to one
        if ($task->milestone_id) {
            $milestone = $task->milestone;
            $totalTasks = $milestone->tasks()->count();
            $completedTasks = $milestone->tasks()->where('status', 'completed')->count();
            $milestone->update(['completion_percentage' => round(($completedTasks / $totalTasks) * 100)]);
        }

        // Update project progress
        app(UpdateProjectProgressAction::class)->execute($project);

        return back()->with('success', 'Task created successfully!');
    }

    public function update(Request $request, ProjectTask $task)
    {
        $user = Auth::user();
        
        // Check if user can update this task
        if (!$task->project->canUserUpdateTask($user, $task)) {
            abort(403, 'You do not have permission to update this task.');
        }

        // Regular employees can ONLY update status to completed
        $isEmployee = !$task->project->canUserManageTasks($user) && $task->assigned_to === $user->id;

        if ($isEmployee) {
            // Employee can only update status
            $validated = $request->validate([
                'status' => 'required|in:todo,in_progress,review,completed,blocked',
            ]);
        } else {
            // PM or Manager can update everything
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:todo,in_progress,review,completed,blocked',
                'priority' => 'sometimes|in:low,medium,high,urgent',
                'milestone_id' => 'nullable|exists:project_milestones,id',
                'assigned_to' => 'nullable|exists:users,id',
                'due_date' => 'nullable|date',
                'actual_hours' => 'nullable|integer|min:0',
            ]);
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $validated['completed_at'] = now();
        }

        $task->update($validated);

        // Update milestone progress if task belongs to one
        if ($task->milestone_id) {
            $milestone = $task->milestone;
            $milestoneTasksCount = $milestone->tasks()->count();
            $completedTasksCount = $milestone->tasks()->where('status', 'completed')->count();
            
            if ($milestoneTasksCount > 0) {
                $progress = round(($completedTasksCount / $milestoneTasksCount) * 100);
                $milestone->update(['completion_percentage' => $progress]);
                
                // Update project progress
                app(UpdateProjectProgressAction::class)->execute($task->project);
            }
        }

        return back()->with('success', 'Task updated successfully!');
    }

    public function destroy(ProjectTask $task)
    {
        $user = Auth::user();
        
        if (!$task->project->canUserManageTasks($user)) {
            abort(403, 'You do not have permission to delete tasks.');
        }

        $project = $task->project;
        $milestoneId = $task->milestone_id;
        
        $task->delete();

        // Update milestone progress if task was linked
        if ($milestoneId) {
            $milestone = \App\Models\ProjectMilestone::find($milestoneId);
            if ($milestone) {
                $totalTasks = $milestone->tasks()->count();
                if ($totalTasks > 0) {
                    $completedTasks = $milestone->tasks()->where('status', 'completed')->count();
                    $milestone->update(['completion_percentage' => round(($completedTasks / $totalTasks) * 100)]);
                } else {
                    $milestone->update(['completion_percentage' => 0]);
                }
            }
        }

        // Update project progress
        app(UpdateProjectProgressAction::class)->execute($project);

        return back()->with('success', 'Task deleted successfully!');
    }
}
