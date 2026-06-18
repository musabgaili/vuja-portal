<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\ThreeDRequest;
use App\Models\ThreeDRequestFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ThreeDRequestController extends Controller
{
    public const TYPES = ['printing', 'design'];

    public const STATUSES = ['submitted', 'assigned', 'in_progress', 'completed', 'rejected', 'cancelled'];

    public const MATERIALS = ['PLA', 'ABS', 'PETG', 'TPU (Flexible)', 'Resin (SLA)', 'Nylon', 'Other'];

    public const OUTPUT_FORMATS = ['STL', 'OBJ', 'STEP', '3MF', 'FBX', 'Other'];

    public const COMPLEXITIES = ['simple', 'moderate', 'complex'];

    // ---------------- CLIENT SIDE ----------------

    public function index()
    {
        $requests = ThreeDRequest::where('user_id', Auth::id())
            ->with('assignedTo')->withCount('files')->latest()->paginate(12);

        return view('threed.index', compact('requests'));
    }

    public function create(Request $request)
    {
        $type = in_array($request->query('type'), self::TYPES, true) ? $request->query('type') : 'printing';

        return view('threed.create', [
            'type' => $type,
            'materials' => self::MATERIALS,
            'outputFormats' => self::OUTPUT_FORMATS,
            'complexities' => self::COMPLEXITIES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:'.implode(',', self::TYPES),
            'title' => 'required|string|max:200',
            'description' => 'required|string|min:15',
            // Printing
            'material' => 'nullable|string|max:120',
            'color' => 'nullable|string|max:120',
            'quantity' => 'nullable|integer|min:1|max:100000',
            'dimensions' => 'nullable|string|max:160',
            'finish' => 'nullable|string|max:160',
            // Design
            'output_format' => 'nullable|string|max:60',
            'complexity' => 'nullable|in:'.implode(',', self::COMPLEXITIES),
            'reference_links' => 'nullable|string|max:2000',
            // Shared
            'budget_range' => 'nullable|string|max:120',
            'timeline' => 'nullable|string|max:120',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:20480|mimes:pdf,png,jpg,jpeg,gif,webp,zip,txt,csv,svg,stl,obj,step,stp,3mf,fbx,dwg,dxf',
        ]);

        $threeD = ThreeDRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'material' => $validated['material'] ?? null,
            'color' => $validated['color'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'dimensions' => $validated['dimensions'] ?? null,
            'finish' => $validated['finish'] ?? null,
            'output_format' => $validated['output_format'] ?? null,
            'complexity' => $validated['complexity'] ?? null,
            'reference_links' => $validated['reference_links'] ?? null,
            'budget_range' => $validated['budget_range'] ?? null,
            'timeline' => $validated['timeline'] ?? null,
            'status' => 'submitted',
        ]);

        $this->storeFiles($request, $threeD);

        return redirect()->route('threed.show', $threeD)
            ->with('success', __('portal.threed.submitted'));
    }

    public function show(ThreeDRequest $threed)
    {
        $this->authorize('view', $threed);
        $threed->load(['user', 'assignedTo', 'files']);

        return view('threed.show', ['threed' => $threed]);
    }

    public function downloadFile(ThreeDRequestFile $file)
    {
        $this->authorize('view', $file->threeDRequest);

        abort_unless(Storage::disk('private')->exists($file->path), 404);

        return Storage::disk('private')->download($file->path, $file->original_name);
    }

    // ---------------- MANAGER / EMPLOYEE SIDE ----------------

    public function managerIndex(Request $request)
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $query = ThreeDRequest::with(['user', 'assignedTo'])->withCount('files');
        if ($user->isEmployee()) {
            $query->where('assigned_to', $user->id);
        }
        if (in_array($request->query('type'), self::TYPES, true)) {
            $query->where('type', $request->query('type'));
        }

        return view('threed.manager.index', [
            'requests' => $query->latest()->paginate(15)->withQueryString(),
            'activeType' => $request->query('type'),
        ]);
    }

    public function managerShow(ThreeDRequest $threed)
    {
        $this->authorize('manage', $threed);
        $threed->load(['user', 'assignedTo', 'files']);

        return view('threed.manager.show', [
            'threed' => $threed,
            'employees' => User::where('type', 'internal')->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function assign(Request $request, ThreeDRequest $threed)
    {
        $this->authorize('manage', $threed);

        $validated = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $threed->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => $threed->status === 'submitted' ? 'assigned' : $threed->status,
        ]);

        return back()->with('success', __('portal.threed.assigned'));
    }

    public function updateStatus(Request $request, ThreeDRequest $threed)
    {
        $this->authorize('manage', $threed);

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', self::STATUSES),
            'manager_notes' => 'nullable|string|max:5000',
        ]);

        $threed->update($validated);

        return back()->with('success', __('portal.threed.status_updated'));
    }

    /** Turn a completed 3D request into a project + funnel entry (quoted via the Scope Planner). */
    public function convertToProject(ThreeDRequest $threed)
    {
        $this->authorize('manage', $threed);

        if ($threed->status !== 'completed') {
            return back()->withErrors(['error' => __('portal.threed.convert_only_completed')]);
        }

        $already = $threed->isConvertedToProject();

        $specLines = [];
        if ($threed->isPrinting()) {
            if ($threed->material) { $specLines[] = 'Material: '.$threed->material; }
            if ($threed->color) { $specLines[] = 'Color: '.$threed->color; }
            if ($threed->quantity) { $specLines[] = 'Quantity: '.$threed->quantity; }
            if ($threed->dimensions) { $specLines[] = 'Dimensions: '.$threed->dimensions; }
            if ($threed->finish) { $specLines[] = 'Finish: '.$threed->finish; }
        } else {
            if ($threed->output_format) { $specLines[] = 'Output format: '.$threed->output_format; }
            if ($threed->complexity) { $specLines[] = 'Complexity: '.$threed->complexity; }
            if ($threed->reference_links) { $specLines[] = 'References: '.$threed->reference_links; }
        }
        if ($threed->timeline) { $specLines[] = 'Timeline: '.$threed->timeline; }

        $project = app(\App\Services\ServiceProjectConverter::class)->convert($threed, [
            'title' => $threed->title,
            'description' => $threed->description,
            'scope' => trim(implode("\n", $specLines)),
            'client_id' => $threed->user_id,
            'project_manager_id' => $threed->assigned_to,
            'source_label' => $threed->isPrinting() ? '3D Printing' : '3D Design',
        ]);

        return redirect()->route('projects.manager.show', $project)
            ->with($already ? 'info' : 'success', $already ? __('portal.threed.project_exists') : __('portal.threed.project_created'));
    }

    private function storeFiles(Request $request, ThreeDRequest $threeD): void
    {
        foreach ((array) $request->file('files', []) as $file) {
            if (! $file) {
                continue;
            }
            // Private disk: client models/specs must only be reachable via the
            // authorize()-gated downloadFile route, never a public /storage URL.
            $path = $file->store('three-d/'.$threeD->id, 'private');
            ThreeDRequestFile::create([
                'three_d_request_id' => $threeD->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
