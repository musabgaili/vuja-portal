<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Manager-only CRM pipeline stage editor: add / rename / recolour / reorder /
 * (de)activate the Kanban columns. Guards prevent orphaning open deals.
 */
class PipelineStageController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::user()->isManager(), 403);
    }

    public function index()
    {
        $this->guard();

        $counts = Opportunity::query()
            ->selectRaw('stage, count(*) as c')->groupBy('stage')->pluck('c', 'stage');

        return view('crm.stages.index', [
            'stages' => PipelineStage::ordered()->get(),
            'counts' => $counts,
            'colors' => PipelineStage::COLORS,
            'wonCount' => (int) ($counts['won'] ?? 0),
            'lostCount' => (int) ($counts['lost'] ?? 0),
        ]);
    }

    public function store(Request $request)
    {
        $this->guard();

        $validated = $request->validate([
            'label' => 'required|string|max:60',
            'color' => ['required', Rule::in(PipelineStage::COLORS)],
        ]);

        $key = $this->uniqueKey($validated['label']);

        PipelineStage::create([
            'key' => $key,
            'label' => $validated['label'],
            'color' => $validated['color'],
            'position' => (int) PipelineStage::max('position') + 1,
            'is_active' => true,
        ]);

        return redirect()->route('crm-stages.index')->with('success', __('portal.crm_stages.created'));
    }

    public function update(Request $request, PipelineStage $stage)
    {
        $this->guard();

        $validated = $request->validate([
            'label' => 'required|string|max:60',
            'color' => ['required', Rule::in(PipelineStage::COLORS)],
            'is_active' => 'nullable|boolean',
        ]);

        $makeInactive = ! $request->boolean('is_active');
        if ($makeInactive && $stage->is_active) {
            // Don't hide a column that still holds open deals.
            $inUse = Opportunity::where('stage', $stage->key)->count();
            if ($inUse > 0) {
                return back()->withErrors(['stage' => __('portal.crm_stages.cannot_deactivate', ['count' => $inUse])]);
            }
        }

        $stage->update([
            'label' => $validated['label'],
            'color' => $validated['color'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('crm-stages.index')->with('success', __('portal.crm_stages.updated'));
    }

    /** Move a stage one position up or down (swaps with its neighbour). */
    public function move(Request $request, PipelineStage $stage)
    {
        $this->guard();
        $dir = $request->input('direction') === 'up' ? 'up' : 'down';

        $neighbour = PipelineStage::query()
            ->when($dir === 'up',
                fn ($q) => $q->where('position', '<', $stage->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $stage->position)->orderBy('position'))
            ->first();

        if ($neighbour) {
            $p = $stage->position;
            $stage->update(['position' => $neighbour->position]);
            $neighbour->update(['position' => $p]);
        }

        return redirect()->route('crm-stages.index');
    }

    public function destroy(PipelineStage $stage)
    {
        $this->guard();

        $inUse = Opportunity::where('stage', $stage->key)->count();
        if ($inUse > 0) {
            return back()->withErrors(['stage' => __('portal.crm_stages.cannot_delete', ['count' => $inUse])]);
        }

        $stage->delete();

        return redirect()->route('crm-stages.index')->with('success', __('portal.crm_stages.deleted'));
    }

    /** Slugify a label into a unique stage key. */
    private function uniqueKey(string $label): string
    {
        $base = Str::slug($label, '_') ?: 'stage';
        $key = $base;
        $i = 2;
        while (PipelineStage::where('key', $key)->exists()) {
            $key = $base.'_'.$i++;
        }

        return $key;
    }
}
