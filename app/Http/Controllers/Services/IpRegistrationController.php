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
            'documents.*' => 'nullable|file|max:10240',
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
        $user = Auth::user();
        
        if ($user->isClient() && $ip->user_id !== $user->id) {
            abort(403);
        }

        $ip->load(['user', 'assignedTo']);
        
        return view('ip.show', compact('ip'));
    }

    public function bookMeeting(Request $request, IpRegistration $ip)
    {
        // Check if consultant is assigned
        if (!$ip->assigned_to) {
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
        
        if (!$user->isManager() && !$user->isEmployee()) {
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
        
        if (!$user->isInternal()) {
            abort(403);
        }

        $ip->load(['user', 'assignedTo']);
        $employees = User::where('role', 'employee')->get();

        return view('ip.manager.show', compact('ip', 'employees'));
    }

    public function assign(Request $request, IpRegistration $ip)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $ip->update(['assigned_to' => $validated['assigned_to']]);

        return back()->with('success', 'Employee assigned successfully!');
    }

    public function confirmMeeting(Request $request, IpRegistration $ip)
    {
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
        $validated = $request->validate([
            'status' => 'required|in:documentation,filing,registered,completed',
            'registration_number' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'registered' && isset($validated['registration_number'])) {
            $updateData['registration_number'] = $validated['registration_number'];
            $updateData['registered_at'] = now();
        }

        $ip->update($updateData);

        return back()->with('success', 'Status updated successfully!');
    }
}
