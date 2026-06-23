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
            'files' => 'nullable|array|max:10',
            'files.*' => 'nullable|file|max:20480|mimes:pdf,doc,docx,png,jpg,jpeg,gif,webp,zip,txt,mp3,wav,mp4,mov',
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
        $this->authorize('view', $copyright);

        $copyright->load(['user', 'assignedTo']);

        return view('copyright.show', compact('copyright'));
    }

    public function bookMeeting(Request $request, CopyrightRegistration $copyright)
    {
        $this->authorize('update', $copyright);

        // Check if consultant is assigned
        if (! $copyright->assigned_to) {
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

        if (! $user->isManager() && ! $user->isEmployee() && ! $user->isProjectManager()) {
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

        $this->authorize('manage', $copyright);

        $copyright->load(['user', 'assignedTo']);
        $employees = User::where('role', 'employee')->get();

        return view('copyright.manager.show', compact('copyright', 'employees'));
    }

    public function assign(Request $request, CopyrightRegistration $copyright)
    {
        $this->authorize('manage', $copyright);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $copyright->update(['assigned_to' => $validated['assigned_to']]);

        return back()->with('success', 'Employee assigned successfully!');
    }

    public function confirmMeeting(Request $request, CopyrightRegistration $copyright)
    {
        $this->authorize('manage', $copyright);

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
        $this->authorize('manage', $copyright);
        // Same scope as the work panel: only the assignee, a manager, or a PM may record the outcome.
        $user = Auth::user();
        abort_unless((int) $copyright->assigned_to === (int) $user->id || $user->isManager() || $user->isProjectManager(), 403);

        $validated = $request->validate([
            'status' => 'required|in:filing,registered,completed',
            'copyright_number' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];

        // Persist the number whenever it is provided; only stamp registered_at on the registered status.
        if (! empty($validated['copyright_number'])) {
            $updateData['copyright_number'] = $validated['copyright_number'];
        }
        if ($validated['status'] === 'registered') {
            $updateData['registered_at'] = now();
        }

        $copyright->update($updateData);

        return back()->with('success', 'Status updated successfully!');
    }

    /** Turn a registered/completed copyright request into a project + funnel entry. */
    public function convertToProject(CopyrightRegistration $copyright)
    {
        $this->authorize('manage', $copyright);

        if (! in_array($copyright->status, ['registered', 'completed'], true)) {
            return back()->withErrors(['error' => 'Only registered or completed copyright requests can be converted to projects.']);
        }

        $already = $copyright->isConvertedToProject();

        $project = app(\App\Services\ServiceProjectConverter::class)->convert($copyright, [
            'title' => $copyright->title,
            'description' => $copyright->work_description,
            'scope' => 'Work type: '.$copyright->work_type.($copyright->copyright_number ? "\nCopyright #: ".$copyright->copyright_number : ''),
            'client_id' => $copyright->user_id,
            'project_manager_id' => $copyright->assigned_to,
            'source_label' => 'Copyright',
        ]);

        return redirect()->route('projects.manager.show', $project)
            ->with($already ? 'info' : 'success', $already ? 'Project already exists!' : 'Project created from copyright registration — added to the sales funnel.');
    }
}
