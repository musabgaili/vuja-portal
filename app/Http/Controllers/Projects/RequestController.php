<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RequestController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        if (!$user->isClient() || $project->client_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'request' => 'required|string',
        ]);

        $projectRequest = ProjectRequest::create([
            'project_id' => $project->id,
            'client_id' => $user->id,
            'subject' => $validated['subject'],
            'request' => $validated['request'],
            'status' => 'open',
        ]);

        // Send email to Account Manager and PM
        $this->sendRequestNotifications($projectRequest);

        return back()->with('success', 'Request submitted. The team has been notified.');
    }

    public function respond(Request $request, ProjectRequest $projectRequest)
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $validated = $request->validate([
            'response' => 'required|string',
        ]);

        $projectRequest->update([
            'status' => 'completed',
            'handled_by' => $user->id,
            'handled_at' => now(),
            'response' => $validated['response'],
        ]);

        // Notify client
        Mail::to($projectRequest->client->email)
            ->send(new \App\Mail\RequestResponded($projectRequest));

        return back()->with('success', 'Response sent to client.');
    }

    protected function sendRequestNotifications(ProjectRequest $projectRequest)
    {
        $recipients = collect();

        // Project Manager
        if ($projectRequest->project->projectManager) {
            $recipients->push($projectRequest->project->projectManager->email);
        }

        // Account Manager
        if ($projectRequest->project->accountManager) {
            $recipients->push($projectRequest->project->accountManager->email);
        }

        $uniqueRecipients = $recipients->unique();
        
        foreach ($uniqueRecipients as $email) {
            Mail::to($email)->send(new \App\Mail\RequestReceived($projectRequest));
        }
    }
}
