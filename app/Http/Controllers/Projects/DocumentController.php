<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Project $project)
    {
        $user = Auth::user();

        $this->authorize('view', $project);

        $documents = $project->documents()->with('uploadedBy')->latest()->get();

        return view('projects.documents.index', compact('project', 'documents'));
    }

    public function store(Request $request, Project $project)
    {
        $user = Auth::user();

        // Client or PM can upload
        $this->authorize('view', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:20480', // 20MB
            'tag' => 'required|string|in:initial,design,development,final,other',
            'comment' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('project-documents', 'private');

        ProjectDocument::create([
            'project_id' => $project->id,
            'uploaded_by' => $user->id,
            'title' => $validated['title'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'tag' => $validated['tag'],
            'comment' => $validated['comment'],
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
        ]);

        return $this->respond($request, 'Document uploaded successfully!');
    }

    public function update(Request $request, ProjectDocument $document)
    {
        $user = Auth::user();

        $this->authorize('update', $document);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tag' => 'required|string|in:initial,design,development,final,other',
            'comment' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
        ]);

        // Update file if provided
        if ($request->hasFile('file')) {
            Storage::disk('private')->delete($document->file_path);
            $file = $request->file('file');
            $validated['file_path'] = $file->store('project-documents', 'private');
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
        }

        $document->update($validated);

        return $this->respond($request, 'Document updated successfully!');
    }

    public function download(ProjectDocument $document)
    {
        $user = Auth::user();

        $this->authorize('view', $document);

        return Storage::disk('private')->download($document->file_path, $document->title.'.'.$document->file_type);
    }

    public function destroy(Request $request, ProjectDocument $document)
    {
        $user = Auth::user();

        $this->authorize('delete', $document);

        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return $this->respond($request, 'Document deleted successfully!');
    }

    protected function respond(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
