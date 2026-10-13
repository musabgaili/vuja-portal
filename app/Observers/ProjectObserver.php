<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectObserver
{
    public function creating(Project $project): void
    {
        if (! $project->created_by && Auth::id()) {
            $project->created_by = Auth::id();
        }
    }
}
