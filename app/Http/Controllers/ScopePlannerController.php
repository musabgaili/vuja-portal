<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\Quote;
use App\Models\StockItem;
use App\Services\GeminiScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScopePlannerController extends Controller
{
    public const CATEGORIES = ['company', 'student', 'entrepreneur'];

    public function __construct(private GeminiScopeService $gemini)
    {
    }

    /** Persist the current scope + selected inventory/stock as a Quote (CRM bridge). */
    public function saveQuote(Request $request)
    {
        abort_unless(Auth::user()->isInternal(), 403);

        $validated = $request->validate([
            'project_type' => 'required|string|max:160',
            'scope' => 'nullable|string',
            'title' => 'nullable|string|max:200',
            'items' => 'array',
            'items.*' => 'integer|exists:inventory_items,id',
            'stock_items' => 'array',
            'stock_items.*' => 'integer|exists:stock_items,id',
            'qty' => 'array',
            'stock_qty' => 'array',
            'customer_category' => 'nullable|in:'.implode(',', self::CATEGORIES),
            'opportunity_id' => 'nullable|exists:opportunities,id',
        ]);

        $category = $validated['customer_category'] ?? 'company';
        $items = InventoryItem::whereIn('id', $request->input('items', []))->get();
        $stock = StockItem::whereIn('id', $request->input('stock_items', []))->get();

        if ($items->isEmpty() && $stock->isEmpty()) {
            return back()->withErrors(['items' => 'Select at least one inventory or stock item before saving a quote.'])->withInput();
        }

        $opp = ! empty($validated['opportunity_id']) ? Opportunity::find($validated['opportunity_id']) : null;
        $qty = $request->input('qty', []);
        $stockQty = $request->input('stock_qty', []);

        $quote = Quote::create([
            'title' => $validated['title'] ?? $validated['project_type'],
            'scope' => $validated['scope'] ?? null,
            'status' => 'draft',
            'customer_category' => $category,
            'created_by' => Auth::id(),
            'opportunity_id' => $opp?->id,
            'company_id' => $opp?->company_id,
            'contact_id' => $opp?->contact_id,
            'client_id' => $opp?->client_id,
        ]);

        $totalInternal = 0;
        $totalClient = 0;

        foreach ($items as $item) {
            $line = $this->inventoryLine($item, (int) ($qty[$item->id] ?? 1));
            $totalInternal += $line['line_internal'];
            $totalClient += $line['line_client'];
            $quote->items()->create($line['attrs']);
        }
        foreach ($stock as $item) {
            $line = $this->stockLine($item, (int) ($stockQty[$item->id] ?? 1), $category);
            $totalInternal += $line['line_internal'];
            $totalClient += $line['line_client'];
            $quote->items()->create($line['attrs']);
        }

        $quote->update(['total_internal' => $totalInternal, 'total_client' => $totalClient]);

        return redirect()->route('quotes.show', $quote)->with('success', 'Quote saved from the AI Scope Planner.');
    }

    public function index()
    {
        abort_unless(Auth::user()->isInternal(), 403);

        return view('scope-planner.index', $this->viewData(null));
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
            'stock_items' => 'array',
            'stock_items.*' => 'integer|exists:stock_items,id',
            'qty' => 'array',
            'stock_qty' => 'array',
            'customer_category' => 'nullable|in:'.implode(',', self::CATEGORIES),
        ]);

        $category = $validated['customer_category'] ?? 'company';
        $draft = $this->gemini->draft($validated['project_type'], $validated['requirements'], $validated['budget'] ?? null);
        $scope = ($validated['scope'] ?? null) ?: $draft['scope'];

        $selectedIds = $validated['items'] ?? $draft['suggested'];
        $selectedStockIds = $validated['stock_items'] ?? [];
        $qty = $request->input('qty', []);
        $stockQty = $request->input('stock_qty', []);

        $lines = [];
        $internalTotal = 0;
        $clientGrouped = [];
        $clientTotal = 0;

        foreach (InventoryItem::whereIn('id', $selectedIds)->get() as $item) {
            $line = $this->inventoryLine($item, (int) ($qty[$item->id] ?? 1));
            $internalTotal += $line['line_internal'];
            $clientTotal += $line['line_client'];
            $clientGrouped[$line['category']] = ($clientGrouped[$line['category']] ?? 0) + $line['line_client'];
            $lines[] = $line['preview'];
        }
        foreach (StockItem::whereIn('id', $selectedStockIds)->get() as $item) {
            $line = $this->stockLine($item, (int) ($stockQty[$item->id] ?? 1), $category);
            $internalTotal += $line['line_internal'];
            $clientTotal += $line['line_client'];
            $clientGrouped[$line['category']] = ($clientGrouped[$line['category']] ?? 0) + $line['line_client'];
            $lines[] = $line['preview'];
        }

        return view('scope-planner.index', $this->viewData([
            'project_type' => $validated['project_type'],
            'requirements' => $validated['requirements'],
            'budget' => $validated['budget'] ?? null,
            'scope' => $scope,
            'source' => $draft['source'],
            'selectedIds' => $selectedIds,
            'selectedStockIds' => $selectedStockIds,
            'customer_category' => $category,
            'lines' => $lines,
            'internalTotal' => $internalTotal,
            'clientGrouped' => $clientGrouped,
            'clientTotal' => $clientTotal,
            'margin' => $clientTotal - $internalTotal,
        ]));
    }

    private function viewData(?array $result): array
    {
        return [
            'inventory' => InventoryItem::where('active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'stockInventory' => StockItem::where('is_active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category'),
            'opportunities' => Opportunity::whereIn('stage', PipelineStage::keys())->orderBy('name')->get(),
            'categories' => self::CATEGORIES,
            'result' => $result,
        ];
    }

    /** A normalised line from a legacy InventoryItem (cost + markup). */
    private function inventoryLine(InventoryItem $item, int $qty): array
    {
        $q = max(1, $qty);
        $lineInternal = (float) $item->internal_cost * $q;
        $lineClient = $item->clientPrice() * $q;

        return [
            'line_internal' => $lineInternal,
            'line_client' => $lineClient,
            'category' => $item->category,
            'attrs' => [
                'inventory_item_id' => $item->id,
                'name' => $item->name,
                'category' => $item->category,
                'internal_cost' => $item->internal_cost,
                'markup_percentage' => $item->markup_percentage,
                'qty' => $q,
                'line_internal' => $lineInternal,
                'line_client' => $lineClient,
            ],
            'preview' => [
                'name' => $item->name, 'category' => $item->category,
                'cost' => (float) $item->internal_cost, 'markup' => (float) $item->markup_percentage,
                'qty' => $q, 'line_client' => $lineClient,
            ],
        ];
    }

    /** A normalised line from a StockItem, priced by the customer category tier. */
    private function stockLine(StockItem $item, int $qty, string $category): array
    {
        $q = max(1, $qty);
        $purchase = (float) $item->purchase_price;
        $unit = $item->priceFor($category);
        $lineInternal = $purchase * $q;
        $lineClient = $unit * $q;
        $markup = $purchase > 0 ? round(($unit - $purchase) / $purchase * 100, 2) : 0;
        $cat = $item->category ?: 'Inventory';

        return [
            'line_internal' => $lineInternal,
            'line_client' => $lineClient,
            'category' => $cat,
            'attrs' => [
                'stock_item_id' => $item->id,
                'name' => $item->name,
                'category' => $cat,
                'internal_cost' => $purchase,
                'markup_percentage' => $markup,
                'qty' => $q,
                'line_internal' => $lineInternal,
                'line_client' => $lineClient,
            ],
            'preview' => [
                'name' => $item->name, 'category' => $cat,
                'cost' => $purchase, 'markup' => $markup,
                'qty' => $q, 'line_client' => $lineClient,
            ],
        ];
    }
}
