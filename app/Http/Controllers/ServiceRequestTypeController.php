<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestType;
use App\Models\ServiceRequestStep;
use App\Models\StepFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceRequestTypeController extends Controller
{
    /**
     * Display a listing of service request types.
     */
    public function index()
    {
        $user = Auth::user();
        
         

        $serviceTypes = ServiceRequestType::with(['createdBy', 'steps'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        return view('stepper.service-types.index', compact('serviceTypes'));
    }

    /**
     * Show the form for creating a new service request type.
     */
    public function create()
    {
        $user = Auth::user();
        
         

        return view('stepper.service-types.create');
    }

    /**
     * Store a newly created service request type.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'required|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['created_by'] = $user->id;
        $validated['sort_order'] = ServiceRequestType::max('sort_order') + 1;

        $serviceType = ServiceRequestType::create($validated);

        return redirect()->route('stepper.service-types.show', $serviceType)
            ->with('success', 'Service request type created successfully!');
    }

    /**
     * Display the specified service request type.
     */
    public function show(ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
         

        $serviceType->load(['steps.formFields', 'createdBy']);

        return view('stepper.service-types.show', compact('serviceType'));
    }

    /**
     * Show the form for editing the specified service request type.
     */
    public function edit(ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
         

        return view('stepper.service-types.edit', compact('serviceType'));
    }

    /**
     * Update the specified service request type.
     */
    public function update(Request $request, ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'required|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $serviceType->update($validated);

        return redirect()->route('stepper.service-types.show', $serviceType)
            ->with('success', 'Service request type updated successfully!');
    }

    /**
     * Remove the specified service request type.
     */
    public function destroy(ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
         

        $serviceType->delete();

        return redirect()->route('stepper.service-types.index')
            ->with('success', 'Service request type deleted successfully!');
    }

    /**
     * Show the form for creating a new step.
     */
    public function createStep(ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
         

        return view('stepper.steps.create', compact('serviceType'));
    }

    /**
     * Store a newly created step.
     */
    public function storeStep(Request $request, ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'step_type' => ['required', Rule::in(['form', 'approval', 'assignment', 'external_api'])],
            'step_config' => 'nullable|array',
            'conditions' => 'nullable|array',
            'actions' => 'nullable|array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['service_request_type_id'] = $serviceType->id;
        $validated['step_order'] = $serviceType->steps()->max('step_order') + 1;

        $step = ServiceRequestStep::create($validated);

        return redirect()->route('stepper.steps.show', $step)
            ->with('success', 'Step created successfully!');
    }

    /**
     * Show the form for editing a step.
     */
    public function editStep(ServiceRequestStep $step)
    {
        $user = Auth::user();
        
         

        $step->load('serviceRequestType');

        return view('stepper.steps.edit', compact('step'));
    }

    /**
     * Update the specified step.
     */
    public function updateStep(Request $request, ServiceRequestStep $step)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'step_type' => ['required', Rule::in(['form', 'approval', 'assignment', 'external_api'])],
            'step_config' => 'nullable|array',
            'conditions' => 'nullable|array',
            'actions' => 'nullable|array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $step->update($validated);

        return redirect()->route('stepper.steps.show', $step)
            ->with('success', 'Step updated successfully!');
    }

    /**
     * Remove the specified step.
     */
    public function destroyStep(ServiceRequestStep $step)
    {
        $user = Auth::user();
        
         

        $serviceType = $step->serviceRequestType;
        $step->delete();

        return redirect()->route('stepper.service-types.show', $serviceType)
            ->with('success', 'Step deleted successfully!');
    }

    /**
     * Reorder steps.
     */
    public function reorderSteps(Request $request, ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'step_orders' => 'required|array',
            'step_orders.*' => 'required|integer|exists:service_request_steps,id',
        ]);

        foreach ($validated['step_orders'] as $order => $stepId) {
            ServiceRequestStep::where('id', $stepId)
                ->where('service_request_type_id', $serviceType->id)
                ->update(['step_order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }
}