<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\IpRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IpRegistrationController extends Controller
{
    public function create()
    {
        $ipTypes = ['Patent', 'Trademark', 'Design', 'Copyright', 'Other'];

        return view('ip.create', compact('ipTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'ip_description' => 'required|string',
            'ip_type' => 'required|string',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,webp,zip,txt,csv',
        ]);

        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $documents[] = $file->store('ip-documents', 'private');
            }
        }

        $ip = IpRegistration::create([
            ...$validated,
            'user_id' => Auth::id(),
            'supporting_documents' => $documents,
            'status' => 'submitted',
        ]);

        return redirect()->route('ip.show', $ip)
            ->with('success', 'IP Registration request submitted successfully!');
    }

    public function show(IpRegistration $ip)
    {
        $this->authorize('view', $ip);

        $ip->load(['user', 'assignedTo']);

        return view('ip.show', compact('ip'));
    }

    public function bookMeeting(Request $request, IpRegistration $ip)
    {
        $this->authorize('update', $ip);

        // Check if consultant is assigned
        if (! $ip->assigned_to) {
            return back()->withErrors(['error' => 'You cannot book a meeting until a consultant is assigned to your IP registration request.']);
        }

        $validated = $request->validate([
            'meeting_date' => 'required|date|after:now',
        ]);

        $ip->update([
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

        $query = IpRegistration::with(['user', 'assignedTo']);

        if ($user->isEmployee()) {
            $query->where('assigned_to', $user->id);
        }

        $registrations = $query->latest()->paginate(15);

        return view('ip.manager.index', compact('registrations'));
    }

    public function managerShow(IpRegistration $ip)
    {
        $user = Auth::user();

        $this->authorize('manage', $ip);

        $ip->load(['user', 'assignedTo']);
        $employees = User::where('role', 'employee')->get();

        return view('ip.manager.show', compact('ip', 'employees'));
    }

    public function assign(Request $request, IpRegistration $ip)
    {
        $this->authorize('manage', $ip);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ip->update(['assigned_to' => $validated['assigned_to']]);

        return back()->with('success', 'Employee assigned successfully!');
    }

    public function confirmMeeting(Request $request, IpRegistration $ip)
    {
        $this->authorize('manage', $ip);

        $validated = $request->validate([
            'meeting_link' => 'nullable|url',
        ]);

        $ip->update([
            'meeting_confirmed_at' => now(),
            'meeting_link' => $validated['meeting_link'],
            'status' => 'meeting_confirmed',
        ]);

        return back()->with('success', 'Meeting confirmed!');
    }

    public function updateStatus(Request $request, IpRegistration $ip)
    {
        $this->authorize('manage', $ip);
        // Same scope as the work panel: only the assignee, a manager, or a PM may record the outcome.
        $user = Auth::user();
        abort_unless((int) $ip->assigned_to === (int) $user->id || $user->isManager() || $user->isProjectManager(), 403);

        $validated = $request->validate([
            'status' => 'required|in:documentation,filing,registered,completed',
            'registration_number' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];

        // Persist the number whenever it is provided; only stamp registered_at on the registered status.
        if (! empty($validated['registration_number'])) {
            $updateData['registration_number'] = $validated['registration_number'];
        }
        if ($validated['status'] === 'registered') {
            $updateData['registered_at'] = now();
        }

        $ip->update($updateData);

        return back()->with('success', 'Status updated successfully!');
    }

    /** Turn a registered/completed IP request into a project + funnel entry. */
    public function convertToProject(IpRegistration $ip)
    {
        $this->authorize('manage', $ip);

        if (! in_array($ip->status, ['registered', 'completed'], true)) {
            return back()->withErrors(['error' => 'Only registered or completed IP requests can be converted to projects.']);
        }

        $already = $ip->isConvertedToProject();

        $project = app(\App\Services\ServiceProjectConverter::class)->convert($ip, [
            'title' => $ip->title,
            'description' => $ip->ip_description,
            'scope' => 'IP type: '.$ip->ip_type.($ip->registration_number ? "\nRegistration #: ".$ip->registration_number : ''),
            'client_id' => $ip->user_id,
            'project_manager_id' => $ip->assigned_to,
            'source_label' => 'IP Registration',
        ]);

        return redirect()->route('projects.manager.show', $project)
            ->with($already ? 'info' : 'success', $already ? 'Project already exists!' : 'Project created from IP registration — added to the sales funnel.');
    }
}
