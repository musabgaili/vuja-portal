<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\PrototypeRequest;
use App\Models\PrototypeRequestFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrototypeRequestController extends Controller
{
    public const CATEGORIES = [
        'Web Application', 'Mobile Application', 'Hardware / Electronics',
        'IoT / Embedded', 'AI / Data', 'Industrial / Mechanical', 'Other',
    ];

    public const STATUSES = ['submitted', 'assigned', 'in_progress', 'completed', 'rejected', 'cancelled'];

    // ---------------- CLIENT SIDE ----------------

    public function index()
    {
        $prototypes = PrototypeRequest::where('user_id', Auth::id())
            ->with('assignedTo')->withCount('files')->latest()->paginate(12);

        return view('prototypes.index', compact('prototypes'));
    }

    public function create()
    {
        return view('prototypes.create', ['categories' => self::CATEGORIES]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'nullable|string|max:120',
            'description' => 'required|string|min:20',
            'goals' => 'nullable|string',
            'budget_range' => 'nullable|string|max:120',
            'timeline' => 'nullable|string|max:120',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,webp,zip,txt,csv,dwg,svg',
        ]);

        $prototype = PrototypeRequest::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'],
            'goals' => $validated['goals'] ?? null,
            'budget_range' => $validated['budget_range'] ?? null,
            'timeline' => $validated['timeline'] ?? null,
            'status' => 'submitted',
        ]);

        $this->storeFiles($request, $prototype);

        return redirect()->route('prototypes.show', $prototype)
            ->with('success', __('portal.prototypes.submitted'));
    }

    public function show(PrototypeRequest $prototype)
    {
        $this->authorize('view', $prototype);
        $prototype->load(['user', 'assignedTo', 'files']);

        return view('prototypes.show', compact('prototype'));
    }

    public function downloadFile(PrototypeRequestFile $file)
    {
        $this->authorize('view', $file->prototypeRequest);

        abort_unless(Storage::disk('private')->exists($file->path), 404);

        return Storage::disk('private')->download($file->path, $file->original_name);
    }

    // ---------------- MANAGER / EMPLOYEE SIDE ----------------

    public function managerIndex()
    {
        $user = Auth::user();
        abort_unless($user->isInternal(), 403);

        $query = PrototypeRequest::with(['user', 'assignedTo'])->withCount('files');
        if ($user->isEmployee()) {
            $query->where('assigned_to', $user->id);
        }

        return view('prototypes.manager.index', ['prototypes' => $query->latest()->paginate(15)]);
    }

    public function managerShow(PrototypeRequest $prototype)
    {
        $this->authorize('manage', $prototype);
        $prototype->load(['user', 'assignedTo', 'files']);

        return view('prototypes.manager.show', [
            'prototype' => $prototype,
            'employees' => User::where('type', 'internal')->orderBy('name')->get(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function assign(Request $request, PrototypeRequest $prototype)
    {
        $this->authorize('manage', $prototype);

        $validated = $request->validate(['assigned_to' => 'required|exists:users,id']);
        $prototype->update([
            'assigned_to' => $validated['assigned_to'],
            'status' => $prototype->status === 'submitted' ? 'assigned' : $prototype->status,
        ]);

        return back()->with('success', __('portal.prototypes.assigned'));
    }

    public function updateStatus(Request $request, PrototypeRequest $prototype)
    {
        $this->authorize('manage', $prototype);

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', self::STATUSES),
            'manager_notes' => 'nullable|string|max:5000',
        ]);

        $prototype->update($validated);

        return back()->with('success', __('portal.prototypes.status_updated'));
    }

    /** Turn a completed prototype request into a project + funnel entry. */
    public function convertToProject(PrototypeRequest $prototype)
    {
        $this->authorize('manage', $prototype);

        if ($prototype->status !== 'completed') {
            return back()->withErrors(['error' => 'Only completed prototype requests can be converted to projects.']);
        }

        $already = $prototype->isConvertedToProject();

        $project = app(\App\Services\ServiceProjectConverter::class)->convert($prototype, [
            'title' => $prototype->title,
            'description' => $prototype->description,
            'scope' => trim(($prototype->goals ? 'Goals: '.$prototype->goals."\n" : '').($prototype->timeline ? 'Timeline: '.$prototype->timeline : '')),
            'client_id' => $prototype->user_id,
            'project_manager_id' => $prototype->assigned_to,
            'source_label' => 'Prototype',
        ]);

        return redirect()->route('projects.manager.show', $project)
            ->with($already ? 'info' : 'success', $already ? 'Project already exists!' : 'Project created from prototype — added to the sales funnel.');
    }

    private function storeFiles(Request $request, PrototypeRequest $prototype): void
    {
        foreach ((array) $request->file('files', []) as $file) {
            if (! $file) {
                continue;
            }
            // Private disk: client business plans/specs must only be reachable via
            // the authorize()-gated downloadFile route, never a public /storage URL.
            $path = $file->store('prototypes/'.$prototype->id, 'private');
            PrototypeRequestFile::create([
                'prototype_request_id' => $prototype->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }
}
