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
        
        if (!$project->canUserView($user)) {
            abort(403);
        }

        $documents = $project->documents()->with('uploadedBy')->latest()->get();

        return view('projects.documents.index', compact('project', 'documents'));
    }

    public function store(Request $request, Project $project)
    {
        $user = Auth::user();
        
        // Client or PM can upload
        if (!$project->canUserView($user)) {
            abort(403);
        }

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

        return back()->with('success', 'Document uploaded successfully!');
    }

    public function update(Request $request, ProjectDocument $document)
    {
        $user = Auth::user();
        
        // Only uploader or manager can update
        if ($document->uploaded_by !== $user->id && !$user->isManager()) {
            abort(403);
        }

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

        return back()->with('success', 'Document updated successfully!');
    }

    public function download(ProjectDocument $document)
    {
        $user = Auth::user();
        
        if (!$document->project->canUserView($user)) {
            abort(403);
        }

        return Storage::disk('private')->download($document->file_path, $document->title . '.' . $document->file_type);
    }

    public function destroy(ProjectDocument $document)
    {
        $user = Auth::user();
        
        // Only uploader or manager can delete
        if ($document->uploaded_by !== $user->id && !$user->isManager()) {
            abort(403);
        }

        Storage::disk('private')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Document deleted successfully!');
    }
}
