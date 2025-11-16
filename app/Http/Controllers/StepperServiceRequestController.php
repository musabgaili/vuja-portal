<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestType;
use App\Models\ServiceRequestStep;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StepperServiceRequestController extends Controller
{
    /**
     * Display a listing of available service request types.
     */
    public function index()
    {
        $serviceTypes = ServiceRequestType::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('stepper.client.index', compact('serviceTypes'));
    }

    /**
     * Show the form for creating a new service request.
     */
    public function create(ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            abort(403);
        }

        if (!$serviceType->is_active) {
            abort(404);
        }

        $serviceType->load('activeSteps.formFields');

        return view('stepper.client.create', compact('serviceType'));
    }

    /**
     * Store a newly created service request.
     */
    public function store(Request $request, ServiceRequestType $serviceType)
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            abort(403);
        }

        if (!$serviceType->is_active) {
            abort(404);
        }

        $serviceType->load('activeSteps.formFields');

        // Validate all form fields from all steps
        $validationRules = [];
        $validationMessages = [];

        foreach ($serviceType->activeSteps as $step) {
            if ($step->isFormStep()) {
                foreach ($step->formFields as $field) {
                    $fieldRules = $field->getValidationRules();
                    if (!empty($fieldRules)) {
                        $validationRules["step_{$step->id}.{$field->field_name}"] = $fieldRules;
                        $validationMessages["step_{$step->id}.{$field->field_name}.required"] = $field->getValidationMessage();
                    }
                }
            }
        }

        $validated = $request->validate($validationRules, $validationMessages);

        // Create the service request
        $serviceRequest = ServiceRequest::create([
            'user_id' => $user->id,
            'service_request_type_id' => $serviceType->id,
            'type' => $serviceType->slug,
            'title' => $this->generateTitle($serviceType, $validated),
            'description' => $this->generateDescription($serviceType, $validated),
            'status' => 'pending',
            'priority' => 'medium',
            'step_data' => $validated, // Store all step data
            'current_step_id' => $serviceType->activeSteps->first()?->id,
        ]);

        return redirect()->route('stepper.client.show', $serviceRequest)
            ->with('success', 'Service request created successfully!');
    }

    /**
     * Display the specified service request.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        $user = Auth::user();
        
        if (!$user->isClient() || $serviceRequest->user_id !== $user->id) {
            abort(403);
        }

        $serviceRequest->load(['serviceRequestType.activeSteps.formFields']);

        return view('stepper.client.show', compact('serviceRequest'));
    }

    /**
     * Show the form for the next step.
     */
    public function showStep(ServiceRequest $serviceRequest, ServiceRequestStep $step)
    {
        $user = Auth::user();
        
        if (!$user->isClient() || $serviceRequest->user_id !== $user->id) {
            abort(403);
        }

        if ($serviceRequest->service_request_type_id !== $step->service_request_type_id) {
            abort(404);
        }

        $step->load('formFields');

        return view('stepper.client.step', compact('serviceRequest', 'step'));
    }

    /**
     * Process a step submission.
     */
    public function processStep(Request $request, ServiceRequest $serviceRequest, ServiceRequestStep $step)
    {
        $user = Auth::user();
        
        if (!$user->isClient() || $serviceRequest->user_id !== $user->id) {
            abort(403);
        }

        if ($serviceRequest->service_request_type_id !== $step->service_request_type_id) {
            abort(404);
        }

        $step->load('formFields');

        // Validate form fields for this step
        $validationRules = [];
        $validationMessages = [];

        if ($step->isFormStep()) {
            foreach ($step->formFields as $field) {
                $fieldRules = $field->getValidationRules();
                if (!empty($fieldRules)) {
                    $validationRules[$field->field_name] = $fieldRules;
                    $validationMessages["{$field->field_name}.required"] = $field->getValidationMessage();
                }
            }
        }

        $validated = $request->validate($validationRules, $validationMessages);

        // Update service request with step data
        $currentStepData = $serviceRequest->step_data ?? [];
        $currentStepData["step_{$step->id}"] = $validated;
        
        $serviceRequest->update([
            'step_data' => $currentStepData,
            'current_step_id' => $this->getNextStepId($serviceRequest, $step),
        ]);

        // Process step actions
        $this->processStepActions($serviceRequest, $step, $validated);

        $nextStep = $this->getNextStep($serviceRequest, $step);

        if ($nextStep) {
            return redirect()->route('stepper.client.step', [$serviceRequest, $nextStep])
                ->with('success', 'Step completed successfully!');
        } else {
            return redirect()->route('stepper.client.show', $serviceRequest)
                ->with('success', 'All steps completed! Your request is now pending review.');
        }
    }

    /**
     * Generate a title for the service request.
     */
    private function generateTitle(ServiceRequestType $serviceType, array $data): string
    {
        // Try to find a title field in the first step
        $firstStep = $serviceType->activeSteps->first();
        if ($firstStep && $firstStep->isFormStep()) {
            foreach ($firstStep->formFields as $field) {
                if (str_contains(strtolower($field->field_name), 'title') || 
                    str_contains(strtolower($field->field_label), 'title')) {
                    $value = data_get($data, "step_{$firstStep->id}.{$field->field_name}");
                    if ($value) {
                        return $value;
                    }
                }
            }
        }

        return "{$serviceType->name} Request";
    }

    /**
     * Generate a description for the service request.
     */
    private function generateDescription(ServiceRequestType $serviceType, array $data): string
    {
        $description = "Service Request for {$serviceType->name}\n\n";
        
        foreach ($serviceType->activeSteps as $step) {
            if ($step->isFormStep()) {
                $stepData = data_get($data, "step_{$step->id}", []);
                if (!empty($stepData)) {
                    $description .= "{$step->name}:\n";
                    foreach ($step->formFields as $field) {
                        $value = data_get($stepData, $field->field_name);
                        if ($value) {
                            $description .= "- {$field->field_label}: {$value}\n";
                        }
                    }
                    $description .= "\n";
                }
            }
        }

        return $description;
    }

    /**
     * Get the next step ID.
     */
    private function getNextStepId(ServiceRequest $serviceRequest, ServiceRequestStep $currentStep): ?int
    {
        $nextStep = $this->getNextStep($serviceRequest, $currentStep);
        return $nextStep?->id;
    }

    /**
     * Get the next step.
     */
    private function getNextStep(ServiceRequest $serviceRequest, ServiceRequestStep $currentStep): ?ServiceRequestStep
    {
        $serviceType = $serviceRequest->serviceRequestType;
        $currentStepData = $serviceRequest->step_data ?? [];

        $nextSteps = $serviceType->activeSteps
            ->where('step_order', '>', $currentStep->step_order)
            ->sortBy('step_order');

        foreach ($nextSteps as $step) {
            if ($step->shouldShow($currentStepData)) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Process step actions.
     */
    private function processStepActions(ServiceRequest $serviceRequest, ServiceRequestStep $step, array $data): void
    {
        $actions = $step->getActions();

        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'update_status':
                    $serviceRequest->update(['status' => $action['status']]);
                    break;
                case 'assign_to':
                    $serviceRequest->update(['assigned_to' => $action['user_id']]);
                    break;
                case 'send_notification':
                    // TODO: Implement notification system
                    break;
                case 'external_api':
                    // TODO: Implement external API calls
                    break;
            }
        }
    }
}