<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of the user's service requests.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isClient()) {
            $requests = ServiceRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            // Internal users can see all requests
            $requests = ServiceRequest::with(['user', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('service-requests.index', compact('requests'));
    }

    /**
     * Show the form for creating a new service request.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'idea');
        
        return view('service-requests.create', compact('type'));
    }

    /**
     * Store a newly created service request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['idea', 'consultation', 'research', 'copyright'])],
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'requirements' => 'nullable|string',
            'budget_range' => 'nullable|string',
            'timeline' => 'nullable|string',
            'additional_info' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'pending';

        $serviceRequest = ServiceRequest::create($validated);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request submitted successfully!');
    }

    /**
     * Display the specified service request.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
        // Check if user can view this request
        if ($user->isClient() && $serviceRequest->user_id !== $user->id) {
            abort(403);
        }

        $serviceRequest->load(['user', 'assignedTo', 'reviewedBy']);

        return view('service-requests.show', compact('serviceRequest'));
    }

    /**
     * Show the form for editing the specified service request.
     */
    public function edit(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
        // Only clients can edit their own pending requests
        if (!$user->isClient() || $serviceRequest->user_id !== $user->id || !$serviceRequest->isPending()) {
            abort(403);
        }

        return view('service-requests.edit', compact('serviceRequest'));
    }

    /**
     * Update the specified service request.
     */
    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
        // Only clients can edit their own pending requests
        if (!$user->isClient() || $serviceRequest->user_id !== $user->id || !$serviceRequest->isPending()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'requirements' => 'nullable|string',
            'budget_range' => 'nullable|string',
            'timeline' => 'nullable|string',
            'additional_info' => 'nullable|string',
        ]);

        $serviceRequest->update($validated);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request updated successfully!');
    }

    /**
     * Remove the specified service request.
     */
    public function destroy(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
        // Only clients can delete their own pending requests
        if (!$user->isClient() || $serviceRequest->user_id !== $user->id || !$serviceRequest->isPending()) {
            abort(403);
        }

        $serviceRequest->delete();

        return redirect()->route('service-requests.index')
            ->with('success', 'Service request deleted successfully!');
    }

    /**
     * Show the review queue for managers.
     */
    public function reviewQueue()
    {
        $user = Auth::user();
        
         

        $requests = ServiceRequest::with(['user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(10);

        return view('service-requests.review-queue', compact('requests'));
    }

    /**
     * Approve or reject a service request.
     */
    public function review(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'review_notes' => 'nullable|string',
        ]);

        $serviceRequest->update([
            'status' => $validated['action'] === 'approve' ? 'approved' : 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
            'approved_at' => $validated['action'] === 'approve' ? now() : null,
        ]);

        $action = $validated['action'] === 'approve' ? 'approved' : 'rejected';
        
        return redirect()->route('service-requests.review-queue')
            ->with('success', "Service request {$action} successfully!");
    }

    /**
     * Assign a service request to an employee.
     */
    public function assign(Request $request, ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
        if (!$user->isManager() && !$user->isProjectManager()) {
            abort(403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $assignedUser = User::findOrFail($validated['assigned_to']);
        
        // Check if the assigned user is an employee
        if (!$assignedUser->isEmployee()) {
            return back()->withErrors(['assigned_to' => 'Can only assign to employees.']);
        }

        $serviceRequest->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->route('service-requests.show', $serviceRequest)
            ->with('success', 'Service request assigned successfully!');
    }
}