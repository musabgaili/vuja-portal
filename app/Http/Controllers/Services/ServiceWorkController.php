<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\ServiceWorkItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Cross-service work panel: the single place an assigned employee / PM drives
 * their own progress (worker_status), records internal notes, and attaches
 * deliverables for any of the assignable service requests. Polymorphic so one
 * controller serves all seven service types.
 */
class ServiceWorkController extends Controller
{
    /** Route {type} slug => model class. */
    public const TYPES = [
        'prototype'    => \App\Models\PrototypeRequest::class,
        'threed'       => \App\Models\ThreeDRequest::class,
        'ip'           => \App\Models\IpRegistration::class,
        'copyright'    => \App\Models\CopyrightRegistration::class,
        'research'     => \App\Models\ResearchRequest::class,
        'consultation' => \App\Models\ConsultationRequest::class,
        'idea'         => \App\Models\IdeaRequest::class,
    ];

    public const WORKER_STATUSES = ['not_started', 'in_progress', 'submitted_for_review'];

    /** Resolve the parent service model from the {type}/{id} route pair. */
    private function resolve(string $type, int $id): Model
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        return self::TYPES[$type]::findOrFail($id);
    }

    /** Only the assigned worker, a manager, or a PM may act on a request's work panel. */
    private function authorizeWork(Model $model): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isInternal()
            && ((int) $model->assigned_to === (int) $user->id || $user->isManager() || $user->isProjectManager()),
            403
        );
    }

    /** Managers and PMs have full control over any work item; employees only their own. */
    private function canManage(User $user): bool
    {
        return $user->isManager() || $user->isProjectManager();
    }

    public function updateStatus(Request $request, string $type, int $id)
    {
        $model = $this->resolve($type, $id);
        $this->authorizeWork($model);

        $validated = $request->validate([
            'worker_status' => 'required|in:'.implode(',', self::WORKER_STATUSES),
        ]);

        $model->update(['worker_status' => $validated['worker_status']]);

        return back()->with('success', __('portal.service_work.status_saved'));
    }

    public function addNote(Request $request, string $type, int $id)
    {
        $model = $this->resolve($type, $id);
        $this->authorizeWork($model);

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $model->workItems()->create([
            'user_id' => Auth::id(),
            'type' => 'note',
            'content' => $validated['content'],
        ]);

        return back()->with('success', __('portal.service_work.note_added'));
    }

    public function addDeliverable(Request $request, string $type, int $id)
    {
        $model = $this->resolve($type, $id);
        $this->authorizeWork($model);

        $validated = $request->validate([
            'label' => 'nullable|string|max:255',
            'file' => 'required|file|max:20480|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,webp,zip,txt,csv,svg,dwg,stl,obj,step,stp,3mf,fbx,dxf,mp4,mov',
            'is_client_visible' => 'nullable|boolean',
        ]);

        $file = $request->file('file');
        // Private disk: deliverables reach the client only through the
        // authorize()-gated download route, never a public /storage URL.
        $path = $file->store('service-work/'.$type.'/'.$model->id, 'private');

        $model->workItems()->create([
            'user_id' => Auth::id(),
            'type' => 'deliverable',
            'content' => $validated['label'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'is_client_visible' => $request->boolean('is_client_visible'),
        ]);

        return back()->with('success', __('portal.service_work.deliverable_added'));
    }

    public function download(ServiceWorkItem $item)
    {
        abort_unless($item->type === 'deliverable' && $item->file_path, 404);

        $parent = $item->noteable;
        abort_if($parent === null, 404);

        $user = Auth::user();
        $isWorker = $user->isInternal()
            && ((int) $parent->assigned_to === (int) $user->id || $user->isManager() || $user->isProjectManager());
        $isClientView = $item->is_client_visible && (int) $parent->user_id === (int) $user->id;

        abort_unless($isWorker || $isClientView, 403);
        abort_unless(Storage::disk('private')->exists($item->file_path), 404);

        return Storage::disk('private')->download($item->file_path, $item->file_name);
    }

    public function deleteItem(ServiceWorkItem $item)
    {
        $user = Auth::user();
        // The author may remove their own note/deliverable; managers & PMs may remove any.
        abort_unless(
            $user->isInternal() && ((int) $item->user_id === (int) $user->id || $this->canManage($user)),
            403
        );

        if ($item->file_path && Storage::disk('private')->exists($item->file_path)) {
            Storage::disk('private')->delete($item->file_path);
        }

        $item->delete();

        return back()->with('success', __('portal.service_work.item_deleted'));
    }
}
