<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpportunityController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::user()->isInternal(), 403);
    }

    /** Kanban pipeline + forecast summary. */
    public function index()
    {
        $this->guard();

        $stages = config('crm.stages');
        $open = Opportunity::with('owner')->whereIn('stage', array_keys($stages))->latest()->get();

        $columns = [];
        foreach ($stages as $key => $label) {
            $items = $open->where('stage', $key)->values();
            $columns[$key] = [
                'label' => $label,
                'items' => $items,
                'value' => $items->sum('expected_value'),
            ];
        }

        $won = Opportunity::where('stage', 'won');
        $lost = Opportunity::where('stage', 'lost');
        $summary = [
            'open_count' => $open->count(),
            'pipeline_value' => $open->sum('expected_value'),
            'weighted' => $open->sum(fn (Opportunity $o) => $o->weightedValue()),
            'won_count' => (clone $won)->count(),
            'won_value' => (clone $won)->sum('expected_value'),
            'lost_count' => (clone $lost)->count(),
        ];

        return view('crm.index', compact('columns', 'stages', 'summary'));
    }

    public function create()
    {
        $this->guard();

        return view('crm.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->guard();
        $data = $this->validateData($request);
        $data['owner_id'] = $data['owner_id'] ?? Auth::id();
        Opportunity::create($data);

        return redirect()->route('crm.index')->with('success', 'Opportunity added to the pipeline.');
    }

    public function show(Opportunity $opportunity)
    {
        $this->guard();

        return view('crm.show', ['opportunity' => $opportunity->load('owner', 'client', 'project')]);
    }

    public function edit(Opportunity $opportunity)
    {
        $this->guard();

        return view('crm.edit', $this->formData() + ['opportunity' => $opportunity]);
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        $this->guard();
        $opportunity->update($this->validateData($request));

        return redirect()->route('crm.show', $opportunity)->with('success', 'Opportunity updated.');
    }

    /** Drag-and-drop stage change (fetch). */
    public function updateStage(Request $request, Opportunity $opportunity)
    {
        $this->guard();
        $validated = $request->validate([
            'stage' => 'required|in:'.implode(',', array_keys(config('crm.stages'))),
        ]);
        $opportunity->update(['stage' => $validated['stage']]);

        return response()->json(['ok' => true, 'stage' => $opportunity->stage]);
    }

    public function markLost(Request $request, Opportunity $opportunity)
    {
        $this->guard();
        $validated = $request->validate(['lost_reason' => 'nullable|string|max:255']);
        $opportunity->update([
            'stage' => 'lost',
            'lost_at' => now(),
            'lost_reason' => $validated['lost_reason'] ?? null,
        ]);

        return redirect()->route('crm.index')->with('success', 'Opportunity marked lost.');
    }

    /** Win the deal and convert it into a delivery Project. */
    public function convert(Opportunity $opportunity)
    {
        $this->guard();

        if ($opportunity->converted_project_id) {
            return redirect()->route('projects.manager.show', $opportunity->project)
                ->with('info', 'This opportunity is already a project.');
        }

        $project = Project::create([
            'title' => $opportunity->name,
            'description' => $opportunity->description ?: ('Converted from opportunity: '.$opportunity->name),
            'client_id' => $opportunity->client_id,
            'budget' => $opportunity->expected_value,
            'status' => 'awarded',
            'project_manager_id' => $opportunity->owner_id,
            'source_type' => Opportunity::class,
            'source_id' => $opportunity->id,
        ]);

        $opportunity->update([
            'stage' => 'won',
            'won_at' => now(),
            'probability' => 100,
            'converted_project_id' => $project->id,
        ]);

        return redirect()->route('projects.manager.show', $project)
            ->with('success', 'Deal won — project created from the opportunity.');
    }

    public function destroy(Opportunity $opportunity)
    {
        $this->guard();
        $opportunity->delete();

        return redirect()->route('crm.index')->with('success', 'Opportunity deleted.');
    }

    private function formData(): array
    {
        return [
            'owners' => User::where('type', 'internal')->orderBy('name')->get(),
            'clients' => User::where('role', 'client')->orderBy('name')->get(),
            'sources' => config('crm.sources'),
            'stages' => config('crm.stages'),
        ];
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:200',
            'company_name' => 'nullable|string|max:160',
            'contact_name' => 'nullable|string|max:160',
            'email' => 'nullable|email|max:160',
            'phone' => 'nullable|string|max:40',
            'source' => 'nullable|string|max:60',
            'stage' => 'required|in:new,qualified,proposition',
            'expected_value' => 'nullable|numeric|min:0',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'owner_id' => 'nullable|exists:users,id',
            'client_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);
    }
}
