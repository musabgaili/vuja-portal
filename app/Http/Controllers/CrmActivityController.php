<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Contact;
use App\Models\CrmActivity;
use App\Models\Opportunity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmActivityController extends Controller
{
    private const SUBJECTS = [
        'opportunity' => Opportunity::class,
        'contact' => Contact::class,
        'company' => Company::class,
    ];

    private function guard(): void
    {
        abort_unless(Auth::user()->isInternal(), 403);
    }

    /** Log a note/interaction OR schedule a next-action on a record. */
    public function store(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'subject' => 'required|in:'.implode(',', array_keys(self::SUBJECTS)),
            'subject_id' => 'required|integer',
            'action' => 'required|in:log,schedule',
            'type' => 'required|in:call,email,meeting,todo,note',
            'summary' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'due_at' => 'nullable|date',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $model = self::SUBJECTS[$validated['subject']];
        $subject = $model::findOrFail($validated['subject_id']);

        $isLog = $validated['action'] === 'log';
        $subject->activities()->create([
            'user_id' => $validated['user_id'] ?? Auth::id(),
            'created_by' => Auth::id(),
            'type' => $validated['type'],
            'summary' => $validated['summary'],
            'notes' => $validated['notes'] ?? null,
            'due_at' => $isLog ? null : $validated['due_at'],
            'status' => $isLog ? 'done' : 'planned',
            'done_at' => $isLog ? now() : null,
        ]);

        return back()->with('success', $isLog ? 'Logged.' : 'Activity scheduled.');
    }

    public function complete(CrmActivity $activity)
    {
        $this->guard();
        $activity->update(['status' => 'done', 'done_at' => now()]);

        return back()->with('success', 'Activity completed.');
    }

    public function destroy(CrmActivity $activity)
    {
        $this->guard();
        $activity->delete();

        return back()->with('success', 'Activity removed.');
    }

    /** "My Activities" inbox — planned next-actions assigned to me. */
    public function index()
    {
        $this->guard();

        $mine = CrmActivity::with(['subject', 'creator'])
            ->where('user_id', Auth::id())
            ->where('status', 'planned')
            ->orderBy('due_at')
            ->get();

        return view('crm.activities.index', [
            'overdue' => $mine->filter(fn ($a) => $a->isOverdue())->values(),
            'upcoming' => $mine->filter(fn ($a) => ! $a->isOverdue())->values(),
        ]);
    }
}
