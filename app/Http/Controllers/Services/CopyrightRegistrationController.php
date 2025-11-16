<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;

use App\Models\CopyrightRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CopyrightRegistrationController extends Controller
{
    public function create()
    {
        $workTypes = ['Literary Work', 'Artistic Work', 'Musical Work', 'Software', 'Dramatic Work', 'Other'];
        return view('copyright.create', compact('workTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'work_description' => 'required|string',
            'work_type' => 'required|string',
            'files.*' => 'nullable|file|max:20480',
        ]);

        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $files[] = $file->store('copyright-files', 'private');
            }
        }

        $copyright = CopyrightRegistration::create([
            ...$validated,
            'user_id' => Auth::id(),
            'work_files' => $files,
            'status' => 'submitted',
        ]);

        return redirect()->route('copyright.show', $copyright)
            ->with('success', 'Copyright registration request submitted successfully!');
    }

    public function show(CopyrightRegistration $copyright)
    {
        $user = Auth::user();
        
        if ($user->isClient() && $copyright->user_id !== $user->id) {
            abort(403);
        }

        $copyright->load(['user', 'assignedTo']);
        
        return view('copyright.show', compact('copyright'));
    }

    public function bookMeeting(Request $request, CopyrightRegistration $copyright)
    {
        // Check if consultant is assigned
        if (!$copyright->assigned_to) {
            return back()->withErrors(['error' => 'You cannot book a meeting until a consultant is assigned to your copyright registration request.']);
        }

        $validated = $request->validate([
            'meeting_date' => 'required|date|after:now',
        ]);

        $copyright->update([
            'meeting_requested_at' => $validated['meeting_date'],
            'status' => 'meeting_booked',
        ]);

        return back()->with('info', 'Calendar integration coming soon - External API required');
    }

    public function managerIndex()
    {
        $user = Auth::user();
        
        if (!$user->isManager() && !$user->isEmployee()) {
            abort(403);
        }

        $query = CopyrightRegistration::with(['user', 'assignedTo']);
        
        if ($user->isEmployee()) {
            $query->where('assigned_to', $user->id);
        }

        $copyrights = $query->latest()->paginate(15);

        return view('copyright.manager.index', compact('copyrights'));
    }

    public function managerShow(CopyrightRegistration $copyright)
    {
        $user = Auth::user();
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $copyright->load(['user', 'assignedTo']);
        $employees = User::where('role', 'employee')->get();

        return view('copyright.manager.show', compact('copyright', 'employees'));
    }

    public function assign(Request $request, CopyrightRegistration $copyright)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $copyright->update(['assigned_to' => $validated['assigned_to']]);

        return back()->with('success', 'Employee assigned successfully!');
    }

    public function confirmMeeting(Request $request, CopyrightRegistration $copyright)
    {
        $validated = $request->validate([
            'meeting_link' => 'nullable|url',
        ]);

        $copyright->update([
            'meeting_confirmed_at' => now(),
            'meeting_link' => $validated['meeting_link'],
            'status' => 'meeting_confirmed',
        ]);

        return back()->with('success', 'Meeting confirmed!');
    }

    public function updateStatus(Request $request, CopyrightRegistration $copyright)
    {
        $validated = $request->validate([
            'status' => 'required|in:filing,registered,completed',
            'copyright_number' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'registered' && isset($validated['registration_number'])) {
            $updateData['copyright_number'] = $validated['copyright_number'];
            $updateData['registered_at'] = now();
        }

        $copyright->update($updateData);

        return back()->with('success', 'Status updated successfully!');
    }
}
