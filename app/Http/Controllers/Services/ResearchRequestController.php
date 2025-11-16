<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

use App\Models\ResearchRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ResearchRequestController extends Controller
{
    // CLIENT SIDE
    
    public function create()
    {
        return view('research.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'research_topic' => 'required|string',
            'research_details' => 'nullable|string',
            'relevant_links' => 'nullable|string',
            'files.*' => 'nullable|file|max:10240',
        ]);

        $files = [];
        if ($request->hasFile('files')) {
            // foreach ($request->file('files') as $file) {
            //     $files[] = $file->store('research-files', 'private');
            // }
        }

        $research = ResearchRequest::create([
            ...$validated,
            'user_id' => Auth::id(),
            'uploaded_files' => $files,
            'status' => 'nda_pending',
        ]);

        return redirect()->route('research.show', $research)
            ->with('info', 'Please sign NDA and SLA documents to proceed (Digital Signature Integration Required)');
    }

    public function show(ResearchRequest $research)
    {
        $user = Auth::user();
        
        if ($user->isClient() && $research->user_id !== $user->id) {
            abort(403);
        }

        $research->load(['user', 'assignedTo']);
        
        return view('research.show', compact('research'));
    }

    public function signDocuments(Request $request, ResearchRequest $research)
    {
        // Placeholder for digital signature integration
        $research->update([
            'nda_signed_at' => now(),
            'sla_signed_at' => now(),
            'status' => 'nda_signed',
        ]);

        return back()->with('info', 'NDA/SLA signing will be available with Digital Signature API integration');
    }

    public function bookMeeting(Request $request, ResearchRequest $research)
    {
        // Check if consultant is assigned
        if (!$research->assigned_to) {
            return back()->withErrors(['error' => 'You cannot book a meeting until a consultant is assigned to your research request.']);
        }

        $validated = $request->validate([
            'preferred_date' => 'required|date|after:now',
        ]);

        // Placeholder for calendar integration
        $research->update([
            'meeting_scheduled_at' => $validated['preferred_date'],
            'status' => 'meeting_scheduled',
        ]);

        return back()->with('info', 'Calendar integration will be available with Google Calendar API');
    }

    // MANAGER/EMPLOYEE SIDE
    
    public function managerIndex()
    {
        $user = Auth::user();
        
        if (!$user->isManager() && !$user->isEmployee()) {
            abort(403);
        }

        $query = ResearchRequest::with(['user', 'assignedTo']);
        
        if ($user->isEmployee()) {
            $query->where('assigned_to', $user->id);
        }

        $researches = $query->latest()->paginate(15);

        return view('research.manager.index', compact('researches'));
    }

    public function managerShow(ResearchRequest $research)
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $research->load(['user', 'assignedTo']);
        $employees = User::where('role', 'employee')->get();

        return view('research.manager.show', compact('research', 'employees'));
    }

    public function assign(Request $request, ResearchRequest $research)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $research->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'in_progress',
        ]);

        return back()->with('success', 'Research request assigned successfully!');
    }

    public function complete(Request $request, ResearchRequest $research)
    {
        $validated = $request->validate([
            'research_findings' => 'required|string',
        ]);

        $research->update([
            'research_findings' => $validated['research_findings'],
            'status' => 'completed',
        ]);

        return back()->with('success', 'Research marked as completed!');
    }
}
