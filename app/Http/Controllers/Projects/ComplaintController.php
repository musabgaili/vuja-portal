<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectComplaint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ComplaintController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Only client can submit complaints
        if (!$user->isClient() || $project->client_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'complaint' => 'required|string',
        ]);

        $complaint = ProjectComplaint::create([
            'project_id' => $project->id,
            'client_id' => $user->id,
            'subject' => $validated['subject'],
            'complaint' => $validated['complaint'],
            'status' => 'open',
        ]);

        // Send alert emails to Super Manager, Account Manager (if exists), and PM
        $this->sendComplaintAlerts($complaint);

        return back()->with('success', 'Complaint submitted. Management has been notified.');
    }

    public function resolve(Request $request, ProjectComplaint $complaint)
    {
        $user = Auth::user();
        
        if (!$user->isManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'resolution_note' => 'required|string',
        ]);

        $complaint->update([
            'status' => 'resolved',
            'resolved_by' => $user->id,
            'resolved_at' => now(),
            'resolution_note' => $validated['resolution_note'],
        ]);

        // Notify client
        Mail::to($complaint->client->email)
            ->send(new \App\Mail\ComplaintResolved($complaint));

        return back()->with('success', 'Complaint resolved and client notified.');
    }

    protected function sendComplaintAlerts(ProjectComplaint $complaint)
    {
        $recipients = collect();

        // Super Manager (all managers)
        $managers = User::where('type', 'internal')
            ->whereHas('roles', function($q) {
                $q->where('name', 'manager');
            })->get();
        
        foreach ($managers as $manager) {
            $recipients->push($manager->email);
        }

        // Project Manager
        if ($complaint->project->projectManager) {
            $recipients->push($complaint->project->projectManager->email);
        }

        // Account Manager (if role exists in project_people)
        $accountManager = $complaint->project->projectPeople()
            ->where('role', 'account_manager')
            ->first();
        
        if ($accountManager) {
            $recipients->push($accountManager->user->email);
        }

        // Send email to all recipients
        $uniqueRecipients = $recipients->unique();
        
        foreach ($uniqueRecipients as $email) {
            Mail::to($email)->send(new \App\Mail\ComplaintAlert($complaint));
        }
    }
}
