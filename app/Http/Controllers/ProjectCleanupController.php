<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectMilestone;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;

class ProjectCleanupController extends Controller
{
    public function index(Request $request)
    {
        $this->guard($request);

        $projects = Project::query()
            ->with('client:id,name')
            ->when($request->filled('id'), fn ($q) => $q->where('id', $request->integer('id')))
            ->latest('id')
            ->limit(40)
            ->get(['id', 'title', 'status', 'client_id', 'created_at']);

        return view('projects.cleanup', [
            'projects' => $projects,
            'key' => $request->query('key'),
        ]);
    }

    public function destroy(Request $request)
    {
        $this->guard($request);

        $project = Project::with(['documents', 'deliverables', 'milestones', 'tasks'])->find($request->integer('project_id'));
        if (! $project) {
            return $this->back($request)->with('error', 'No project with that id.');
        }

        $label = '#'.$project->id.' '.$project->title;

        try {
            DB::transaction(function () use ($project) {
                $milestoneIds = $project->milestones->pluck('id');
                $taskIds = $project->tasks->pluck('id');

                ProjectComment::query()
                    ->where(function ($q) use ($project, $milestoneIds, $taskIds) {
                        $q->where(fn ($q) => $q->where('commentable_type', Project::class)->where('commentable_id', $project->id));
                        if ($milestoneIds->isNotEmpty()) {
                            $q->orWhere(fn ($q) => $q->where('commentable_type', ProjectMilestone::class)->whereIn('commentable_id', $milestoneIds));
                        }
                        if ($taskIds->isNotEmpty()) {
                            $q->orWhere(fn ($q) => $q->where('commentable_type', ProjectTask::class)->whereIn('commentable_id', $taskIds));
                        }
                    })
                    ->delete();

                Activity::query()
                    ->where('subject_type', Project::class)
                    ->where('subject_id', $project->id)
                    ->delete();

                $project->delete();
            });
        } catch (\Throwable $e) {
            return $this->back($request)->with('error', $e->getMessage());
        }

        foreach ($project->documents as $document) {
            if ($document->file_path) {
                Storage::disk('private')->delete($document->file_path);
            }
        }
        foreach ($project->deliverables as $deliverable) {
            if ($deliverable->file_path) {
                Storage::disk('private')->delete($deliverable->file_path);
            }
        }

        return $this->back($request)->with('ok', 'Deleted '.$label);
    }

    private function back(Request $request)
    {
        return redirect()->route('projects.cleanup', ['key' => $request->query('key')]);
    }

    private function guard(Request $request): void
    {
        $key = (string) config('cleanup.key');
        $given = (string) $request->query('key');

        if ($key === '' || ! hash_equals($key, $given)) {
            abort(404);
        }
    }
}
