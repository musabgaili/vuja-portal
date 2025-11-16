<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Actions\Projects\UpdateProjectProgressAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        if (!$project->canUserManageMilestones($user)) {
            abort(403, 'You do not have permission to create milestones.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $validated['project_id'] = $project->id;
        $validated['milestone_order'] = $project->milestones()->max('milestone_order') + 1;

        ProjectMilestone::create($validated);

        // Update project progress
        app(UpdateProjectProgressAction::class)->execute($project);

        return back()->with('success', 'Milestone added successfully!');
    }

    public function update(Request $request, ProjectMilestone $milestone)
    {
        $user = Auth::user();
        
        if (!$milestone->project->canUserManageMilestones($user)) {
            abort(403, 'You do not have permission to edit milestones.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'due_date' => 'nullable|date',
        ]);

        // Auto-calculate progress based on tasks
        $totalTasks = $milestone->tasks()->count();
        if ($totalTasks > 0) {
            $completedTasks = $milestone->tasks()->where('status', 'completed')->count();
            $validated['completion_percentage'] = round(($completedTasks / $totalTasks) * 100);
        }

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            $validated['completion_percentage'] = 100;
        }

        $milestone->update($validated);

        // Send email to client when milestone is marked as completed
        if ($validated['status'] === 'completed' && $milestone->project->client) {
            \Mail::to($milestone->project->client->email)
                ->send(new \App\Mail\MilestoneCompleted($milestone));
        }

        // Update project progress
        app(UpdateProjectProgressAction::class)->execute($milestone->project);

        return back()->with('success', 'Milestone updated successfully!');
    }

    public function destroy(ProjectMilestone $milestone)
    {
        $user = Auth::user();
        
        if (!$milestone->project->canUserManageMilestones($user)) {
            abort(403, 'You do not have permission to delete milestones.');
        }

        $project = $milestone->project;
        $milestone->delete();

        // Update project progress
        app(UpdateProjectProgressAction::class)->execute($project);

        return back()->with('success', 'Milestone deleted successfully!');
    }

    /**
     * Client approves completed milestone
     */
    public function clientApprove(Request $request, ProjectMilestone $milestone)
    {
        $user = Auth::user();
        
        // Only client can approve their own project milestones
        if (!$user->isClient() || $milestone->project->client_id !== $user->id) {
            abort(403, 'You do not have permission to approve this milestone.');
        }

        // All milestone tasks must be completed or in review
        $incompleteTasks = $milestone->tasks()->whereNotIn('status', ['completed', 'review'])->count();
        if ($incompleteTasks > 0) {
            return back()->withErrors(['error' => "Cannot review: {$incompleteTasks} task(s) are not yet ready."]);
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'approval_note' => 'required_if:action,reject|nullable|string|max:500',
        ]);

        $isApproved = $validated['action'] === 'approve';

        $milestone->update([
            'client_approved' => $isApproved,
            'client_approved_at' => now(),
            'approval_note' => $validated['approval_note'] ?? null,
        ]);

        // Send email notification
        \Mail::to($milestone->project->projectManager?->email ?? config('mail.from.address'))
            ->send(new \App\Mail\MilestoneApproved($milestone, $user));

        return back()->with('success', 'Milestone approved! Notification sent to the team.');
    }

    public function markCompleted(ProjectMilestone $milestone)
    {
        $user = Auth::user();
        
        if (!$milestone->project->canUserManageMilestones($user)) {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        $milestone->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_percentage' => 100,
        ]);

        // Send email to client
        if ($milestone->project->client) {
            \Mail::to($milestone->project->client->email)
                ->send(new \App\Mail\MilestoneCompleted($milestone));
        }

        return response()->json(['success' => true, 'message' => 'Milestone marked as completed and client notified']);
    }
}
