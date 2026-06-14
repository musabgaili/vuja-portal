<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Services\GeminiScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScopePlannerController extends Controller
{
    public function __construct(private GeminiScopeService $gemini)
    {
    }

    public function index()
    {
        abort_unless(Auth::user()->isInternal(), 403);

        return view('scope-planner.index', [
            'inventory' => InventoryItem::where('active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'result' => null,
        ]);
    }

    /** Draft a scope with AI and build the internal vs client pricing views. */
    public function plan(Request $request)
    {
        abort_unless(Auth::user()->isInternal(), 403);

        $validated = $request->validate([
            'project_type' => 'required|string|max:160',
            'requirements' => 'required|string',
            'budget' => 'nullable|string|max:60',
            'scope' => 'nullable|string',
            'items' => 'array',
            'items.*' => 'integer|exists:inventory_items,id',
            'qty' => 'array',
        ]);

        // Use the edited scope if present, otherwise draft a fresh one.
        $draft = $this->gemini->draft($validated['project_type'], $validated['requirements'], $validated['budget'] ?? null);
        $scope = ($validated['scope'] ?? null) ?: $draft['scope'];

        // Selected items: explicit selection, else the AI suggestions on first run.
        $selectedIds = $validated['items'] ?? $draft['suggested'];
        $qty = $request->input('qty', []);
        $items = InventoryItem::whereIn('id', $selectedIds)->get();

        $lines = [];
        $internalTotal = 0;
        $clientGrouped = [];
        $clientTotal = 0;
        foreach ($items as $item) {
            $q = max(1, (int) ($qty[$item->id] ?? 1));
            $lineInternal = (float) $item->internal_cost * $q;
            $lineClient = $item->clientPrice() * $q;
            $internalTotal += $lineInternal;
            $clientTotal += $lineClient;
            $clientGrouped[$item->category] = ($clientGrouped[$item->category] ?? 0) + $lineClient;
            $lines[] = [
                'item' => $item, 'qty' => $q,
                'line_internal' => $lineInternal, 'line_client' => $lineClient,
            ];
        }

        return view('scope-planner.index', [
            'inventory' => InventoryItem::where('active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'result' => [
                'project_type' => $validated['project_type'],
                'requirements' => $validated['requirements'],
                'budget' => $validated['budget'] ?? null,
                'scope' => $scope,
                'source' => $draft['source'],
                'selectedIds' => $selectedIds,
                'lines' => $lines,
                'internalTotal' => $internalTotal,
                'clientGrouped' => $clientGrouped,
                'clientTotal' => $clientTotal,
                'margin' => $clientTotal - $internalTotal,
            ],
        ]);
    }
}
