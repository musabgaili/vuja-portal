<?php

namespace App\Services\Projects;

use App\Models\Project;
use App\Models\ProjectPerson;
use App\Models\User;
use App\Actions\Projects\CreateProjectAction;

class ProjectService
{
    /**
     * Create a new project with team members - Uses Action
     */
    public function createProject(array $data): Project
    {
        return app(CreateProjectAction::class)->execute($data);
    }

    /**
     * Add a person to project team
     */
    public function addProjectPerson(Project $project, int $userId, string $role, bool $canEdit): ProjectPerson
    {
        return ProjectPerson::create([
            'project_id' => $project->id,
            'user_id' => $userId,
            'role' => $role,
            'can_edit' => $canEdit,
        ]);
    }

    /**
     * Remove a person from project team
     */
    public function removeProjectPerson(Project $project, int $userId): bool
    {
        return $project->projectPeople()->where('user_id', $userId)->delete();
    }

    /**
     * Update project manager
     */
    public function assignProjectManager(Project $project, int $userId): void
    {
        // Remove existing project manager
        $project->projectPeople()
            ->where('role', 'project_manager')
            ->delete();

        // Add new project manager
        $this->addProjectPerson($project, $userId, 'project_manager', true);
        $project->update(['project_manager_id' => $userId]);
    }

    /**
     * Get projects for a user based on their role with search/filters
     */
    public function getProjectsForUser(User $user, array $filters = [])
    {
        $query = Project::with(['client', 'projectPeople.user', 'milestones', 'tasks', 'projectManager', 'quotedBy']);
        
        // If employee, show only projects they're assigned to
        if ($user->isEmployee()) {
            $query->whereHas('projectPeople', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Search by name
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by PM
        if (!empty($filters['pm'])) {
            $query->where('project_manager_id', $filters['pm']);
        }

        // Filter by quoter
        if (!empty($filters['quoter'])) {
            $query->where('quoted_by', $filters['quoter']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Calculate project statistics for a user
     */
    public function getProjectStats(User $user = null): array
    {
        // If no user provided, get all stats (for managers)
        if (!$user || $user->isManager()) {
            return [
                'total' => Project::count(),
                'planning' => Project::where('status', 'planning')->count(),
                'quoted' => Project::where('status', 'quoted')->count(),
                'awarded' => Project::where('status', 'awarded')->count(),
                'in_progress' => Project::where('status', 'in_progress')->count(),
                'paused' => Project::where('status', 'paused')->count(),
                'completed' => Project::where('status', 'completed')->count(),
            ];
        }

        // For employees, only count projects they're assigned to
        if ($user->isEmployee()) {
            return [
                'total' => Project::whereHas('projectPeople', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
                'planning' => Project::whereHas('projectPeople', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', 'planning')->count(),
                'in_progress' => Project::whereHas('projectPeople', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', 'in_progress')->count(),
                'completed' => Project::whereHas('projectPeople', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', 'completed')->count(),
            ];
        }

        // Default: return all stats
        return [
            'total' => Project::count(),
            'planning' => Project::where('status', 'planning')->count(),
            'in_progress' => Project::where('status', 'in_progress')->count(),
            'completed' => Project::where('status', 'completed')->count(),
        ];
    }
}

