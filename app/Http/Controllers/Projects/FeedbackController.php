<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function create(Project $project)
    {
        $user = Auth::user();
        
        if ($project->client_id !== $user->id) {
            abort(403);
        }

        if (!$project->isCompleted()) {
            return redirect()->route('projects.client.show', $project)
                ->withErrors(['error' => 'Can only provide feedback for completed projects.']);
        }

        // Check if feedback already exists
        if ($project->feedback) {
            return redirect()->route('projects.client.show', $project)
                ->with('info', 'You have already provided feedback for this project.');
        }

        return view('projects.client.feedback', compact('project'));
    }

    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        if ($project->client_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'quality_rating' => 'nullable|integer|min:1|max:5',
            'timeline_rating' => 'nullable|integer|min:1|max:5',
            'would_recommend' => 'boolean',
        ]);

        ProjectFeedback::create([
            ...$validated,
            'project_id' => $project->id,
            'client_id' => $user->id,
        ]);

        return redirect()->route('projects.client.show', $project)
            ->with('success', 'Thank you for your feedback!');
    }
}
