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

        if (! $user->canUseClientProjectPortal() || $project->client_id !== $user->id) {
            abort(403);
        }

        if (! $project->isCompleted()) {
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

        if (! $user->canUseClientProjectPortal() || $project->client_id !== $user->id) {
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

        // Engagement: a 5-star rating rewards the people who delivered the project.
        if ((int) $validated['rating'] === 5) {
            $engagement = app(\App\Services\EngagementService::class);
            foreach (array_unique(array_filter([$project->project_manager_id, $project->account_manager_id])) as $recipientId) {
                if ($recipient = \App\Models\User::find($recipientId)) {
                    $engagement->award($recipient, 'client_five_star', $project, null, '5-star rating on '.$project->title);
                }
            }
        }

        return redirect()->route('projects.client.show', $project)
            ->with('success', 'Thank you for your feedback!');
    }
}
