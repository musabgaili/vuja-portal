<?php

namespace App\Actions\Projects;

use App\Models\Project;

class UpdateProjectProgressAction
{
    public function execute(Project $project): void
    {
        // Refresh all data
        $project->load('milestones.tasks', 'tasks');
        $milestones = $project->milestones;
        $allTasks = $project->tasks;
        
        // If no tasks at all, progress is 0
        if ($allTasks->count() === 0) {
            $project->update(['completion_percentage' => 0]);
            return;
        }
        
        // If no milestones OR all tasks are unlinked, calculate from all tasks
        $linkedTasks = $allTasks->whereNotNull('milestone_id');
        if ($milestones->count() === 0 || $linkedTasks->count() === 0) {
            $completedTasks = $allTasks->where('status', 'completed')->count();
            $progress = round(($completedTasks / $allTasks->count()) * 100);
            $project->update(['completion_percentage' => $progress]);
            return;
        }

        // Calculate from milestones (only if tasks are linked to them)
        $totalProgress = $milestones->sum('completion_percentage');
        $averageProgress = round($totalProgress / $milestones->count());

        $project->update(['completion_percentage' => $averageProgress]);
    }
}

