<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDeliverable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeliverableController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Only PM or Manager can upload deliverables
        if (!$project->canUserManageTasks($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:51200', // 50MB
        ]);

        $file = $request->file('file');
        $path = $file->store('deliverables', 'private');

        ProjectDeliverable::create([
            'project_id' => $project->id,
            'uploaded_by' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'file_path' => $path,
        ]);

        return back()->with('success', 'Deliverable uploaded successfully!');
    }

    public function download(ProjectDeliverable $deliverable)
    {
        $user = Auth::user();
        
        if (!$deliverable->project->canUserView($user)) {
            abort(403);
        }

        return Storage::disk('private')->download($deliverable->file_path, $deliverable->title);
    }

    public function confirmReceipt(Request $request, ProjectDeliverable $deliverable)
    {
        $user = Auth::user();
        
        // Only client can confirm
        if (!$user->isClient() || $deliverable->project->client_id !== $user->id) {
            abort(403);
        }

        $deliverable->update([
            'client_confirmed' => true,
            'client_confirmed_at' => now(),
        ]);

        // Check if all deliverables confirmed, close project
        $allConfirmed = $deliverable->project->deliverables()->where('client_confirmed', false)->count() === 0;
        
        if ($allConfirmed) {
            $deliverable->project->update([
                'status' => 'completed',
                'actual_end_date' => now(),
            ]);

            // Send email
            \Mail::to($user->email)
                ->send(new \App\Mail\ProjectCompleted($deliverable->project, $user));
        }

        return back()->with('success', 'Deliverable confirmed! ' . ($allConfirmed ? 'Project marked as completed.' : ''));
    }

    public function destroy(ProjectDeliverable $deliverable)
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        Storage::disk('private')->delete($deliverable->file_path);
        $deliverable->delete();

        return back()->with('success', 'Deliverable deleted successfully!');
    }
}
