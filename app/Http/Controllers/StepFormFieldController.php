<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequestStep;
use App\Models\StepFormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StepFormFieldController extends Controller
{
    /**
     * Show the form for creating a new form field.
     */
    public function create(ServiceRequestStep $step)
    {
        $user = Auth::user();
        
         

        return view('stepper.fields.create', compact('step'));
    }

    /**
     * Store a newly created form field.
     */
    public function store(Request $request, ServiceRequestStep $step)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'field_name' => 'required|string|max:255|regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
            'field_label' => 'required|string|max:255',
            'field_type' => ['required', Rule::in([
                'text', 'email', 'number', 'tel', 'url', 'textarea', 
                'select', 'radio', 'checkbox', 'file', 'date', 'datetime-local'
            ])],
            'field_description' => 'nullable|string',
            'field_options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'is_required' => 'boolean',
            'field_config' => 'nullable|array',
        ]);

        $validated['service_request_step_id'] = $step->id;
        $validated['field_order'] = $step->formFields()->max('field_order') + 1;

        $field = StepFormField::create($validated);

        return redirect()->route('stepper.steps.show', $step)
            ->with('success', 'Form field created successfully!');
    }

    /**
     * Show the form for editing a form field.
     */
    public function edit(StepFormField $field)
    {
        $user = Auth::user();
        
         

        $field->load('serviceRequestStep');

        return view('stepper.fields.edit', compact('field'));
    }

    /**
     * Update the specified form field.
     */
    public function update(Request $request, StepFormField $field)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'field_name' => 'required|string|max:255|regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
            'field_label' => 'required|string|max:255',
            'field_type' => ['required', Rule::in([
                'text', 'email', 'number', 'tel', 'url', 'textarea', 
                'select', 'radio', 'checkbox', 'file', 'date', 'datetime-local'
            ])],
            'field_description' => 'nullable|string',
            'field_options' => 'nullable|array',
            'validation_rules' => 'nullable|array',
            'is_required' => 'boolean',
            'field_config' => 'nullable|array',
        ]);

        $field->update($validated);

        return redirect()->route('stepper.steps.show', $field->serviceRequestStep)
            ->with('success', 'Form field updated successfully!');
    }

    /**
     * Remove the specified form field.
     */
    public function destroy(StepFormField $field)
    {
        $user = Auth::user();
        
         

        $step = $field->serviceRequestStep;
        $field->delete();

        return redirect()->route('stepper.steps.show', $step)
            ->with('success', 'Form field deleted successfully!');
    }

    /**
     * Reorder form fields.
     */
    public function reorder(Request $request, ServiceRequestStep $step)
    {
        $user = Auth::user();
        
         

        $validated = $request->validate([
            'field_orders' => 'required|array',
            'field_orders.*' => 'required|integer|exists:step_form_fields,id',
        ]);

        foreach ($validated['field_orders'] as $order => $fieldId) {
            StepFormField::where('id', $fieldId)
                ->where('service_request_step_id', $step->id)
                ->update(['field_order' => $order + 1]);
        }

        return response()->json(['success' => true]);
    }
}