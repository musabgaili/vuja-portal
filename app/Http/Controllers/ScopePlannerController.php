<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Opportunity;
use App\Models\Quote;
use App\Services\GeminiScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScopePlannerController extends Controller
{
    public function __construct(private GeminiScopeService $gemini)
    {
    }

    /** Persist the current scope + selected inventory as a Quote (CRM bridge). */
    public function saveQuote(Request $request)
    {
        abort_unless(Auth::user()->isInternal(), 403);

        $validated = $request->validate([
            'project_type' => 'required|string|max:160',
            'requirements' => 'nullable|string',
            'scope' => 'nullable|string',
            'title' => 'nullable|string|max:200',
            'items' => 'array',
            'items.*' => 'integer|exists:inventory_items,id',
            'qty' => 'array',
            'opportunity_id' => 'nullable|exists:opportunities,id',
        ]);

        $items = InventoryItem::whereIn('id', $request->input('items', []))->get();
        if ($items->isEmpty()) {
            return back()->withErrors(['items' => 'Select at least one inventory item before saving a quote.'])->withInput();
        }

        $opp = ! empty($validated['opportunity_id']) ? Opportunity::find($validated['opportunity_id']) : null;
        $qty = $request->input('qty', []);

        $quote = Quote::create([
            'title' => $validated['title'] ?? $validated['project_type'],
            'scope' => $validated['scope'] ?? null,
            'status' => 'draft',
            'created_by' => Auth::id(),
            'opportunity_id' => $opp?->id,
            'company_id' => $opp?->company_id,
            'contact_id' => $opp?->contact_id,
            'client_id' => $opp?->client_id,
        ]);

        $totalInternal = 0;
        $totalClient = 0;
        foreach ($items as $item) {
            $q = max(1, (int) ($qty[$item->id] ?? 1));
            $lineInternal = (float) $item->internal_cost * $q;
            $lineClient = $item->clientPrice() * $q;
            $totalInternal += $lineInternal;
            $totalClient += $lineClient;
            $quote->items()->create([
                'inventory_item_id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'internal_cost' => $item->internal_cost,
                'markup_percentage' => $item->markup_percentage,
                'qty' => $q,
                'line_internal' => $lineInternal,
                'line_client' => $lineClient,
            ]);
        }
        $quote->update(['total_internal' => $totalInternal, 'total_client' => $totalClient]);

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote saved from the AI Scope Planner.');
    }

    public function index()
    {
        abort_unless(Auth::user()->isInternal(), 403);

        return view('scope-planner.index', [
            'inventory' => InventoryItem::where('active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'opportunities' => Opportunity::whereIn('stage', array_keys(config('crm.stages')))->orderBy('name')->get(),
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
            'opportunities' => Opportunity::whereIn('stage', array_keys(config('crm.stages')))->orderBy('name')->get(),
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
