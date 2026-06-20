<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\InventoryItem;
use App\Models\Opportunity;
use App\Models\PipelineStage;
use App\Models\Quote;
use App\Models\StockItem;
use App\Models\User;
use App\Services\GeminiScopeService;
use App\Services\Scope\QuoteGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScopePlannerController extends Controller
{
    public const CATEGORIES = ['company', 'student', 'entrepreneur'];

    public const LENGTHS = ['short', 'medium', 'long'];

    public const STRUCTURES = ['single', 'multi_scope'];

    public const LANGUAGES = ['ar', 'en'];

    public function __construct(
        private GeminiScopeService $gemini,
        private QuoteGenerationService $generator,
    ) {}

    // ===================================================================
    // Upgraded staged flow: create → (suggest) → confirm items → generate
    // prose → online preview/edit → finalize → export.
    // ===================================================================

    /** The brief form for a new scope document. */
    public function create(Request $request)
    {
        abort_unless(Auth::user()->isInternal(), 403);

        // Option-1 unify: the quick planner (index) hands its inputs over so the
        // employee fills once and continues into the document builder. The brief
        // is seeded from the quick requirements + drafted scope + budget.
        $projectType = trim((string) $request->query('project_type', ''));
        $requirements = trim((string) $request->query('requirements', ''));
        $scope = trim((string) $request->query('scope', ''));
        $budget = trim((string) $request->query('budget', ''));

        $briefParts = array_filter([
            $requirements !== '' ? $requirements : null,
            $scope !== '' ? $scope : null,
            $budget !== '' ? __('portal.scope_planner.budget_line', ['budget' => $budget]) : null,
        ]);

        $prefill = [
            'subject' => $projectType,
            'brief' => implode("\n\n", $briefParts),
        ];

        return view('scope-planner.create', [
            'categories' => self::CATEGORIES,
            'lengths' => self::LENGTHS,
            'structures' => self::STRUCTURES,
            'languages' => self::LANGUAGES,
            'clients' => User::where('role', 'client')->orderBy('name')->get(),
            'companies' => Company::orderBy('name')->get(),
            'opportunities' => Opportunity::whereIn('stage', PipelineStage::keys())->orderBy('name')->get(),
            'prefill' => $prefill,
        ]);
    }

    /** Create the draft (with Q-number) + run the AI item suggestion, then open the editor. */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->isInternal(), 403);

        $data = $request->validate([
            'tier' => 'required|in:'.implode(',', self::CATEGORIES),
            'length' => 'required|in:'.implode(',', self::LENGTHS),
            'structure' => 'required|in:'.implode(',', self::STRUCTURES),
            'language' => 'required|in:'.implode(',', self::LANGUAGES),
            'subject' => 'nullable|string|max:200',
            'title' => 'nullable|string|max:200',
            'beneficiary' => 'nullable|string|max:200',
            'client_ref' => 'nullable|string|max:120',
            'brief' => 'required|string|min:10',
            'client_id' => 'nullable|exists:users,id',
            'company_id' => 'nullable|exists:companies,id',
            'opportunity_id' => 'nullable|exists:opportunities,id',
        ]);

        $quote = $this->generator->createDraft($data, Auth::id());

        // AI-suggest items AND persist them so Step 2 shows them pre-filled. (They
        // were previously only flashed as a hint, so the items table came up empty
        // and the employee had to re-enter everything.)
        $suggestion = $this->generator->suggestItems($quote);
        if (! empty($suggestion['components']) || ! empty($suggestion['services'])) {
            $this->generator->saveItems($quote, $suggestion['components'], $suggestion['services']);
        }
        session()->flash('scope_suggestion', $suggestion);

        return redirect()->route('scope-planner.show', ['quote' => $quote, 'step' => 'items'])
            ->with('success', __('portal.scope_planner.draft_created', ['number' => $quote->quote_number]));
    }

    /** The editor / online preview surface — Step 2 (items) or Step 3 (document). */
    public function show(Request $request, Quote $quote)
    {
        $this->authorizeQuote($quote);
        $quote->load(['items', 'scopes', 'milestones', 'client']);

        // Step 2 = items, Step 3 = document. Default to whichever the quote is up
        // to: items until sections have been generated, document afterwards.
        $step = $request->query('step');
        if (! in_array($step, ['items', 'document'], true)) {
            $step = empty($quote->ai_content) ? 'items' : 'document';
        }

        return view('scope-planner.editor', [
            'quote' => $quote,
            'step' => $step,
            'suggestion' => session('scope_suggestion'),
            'services' => app(\App\ScopePlanner\Contracts\PricingToolContract::class)->services(),
            // Full models (not a column subset) so priceFor() can resolve the tier
            // price for the manual/inventory component price auto-fill in Step 2.
            'stockItems' => StockItem::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
            'lengths' => self::LENGTHS,
        ]);
    }

    /** Re-suggest items from the brief (AI call 1, on demand). */
    public function suggest(Quote $quote)
    {
        $this->authorizeEditable($quote);

        return redirect()->route('scope-planner.show', $quote)
            ->with('scope_suggestion', $this->generator->suggestItems($quote))
            ->with('success', __('portal.scope_planner.suggested'));
    }

    /** Persist the employee-confirmed components + services, then reprice. */
    public function saveItems(Request $request, Quote $quote)
    {
        $this->authorizeEditable($quote);

        $data = $request->validate([
            'components' => 'array',
            'components.*.stock_item_id' => 'nullable|exists:stock_items,id',
            'components.*.name' => 'nullable|string|max:200',
            'components.*.qty' => 'nullable|integer|min:1',
            'components.*.internal_cost' => 'nullable|numeric|min:0',
            'components.*.unit_price' => 'nullable|numeric|min:0',
            'services' => 'array',
            'services.*.pricing_rule_id' => 'nullable|exists:pricing_rules,id',
            'services.*.name' => 'nullable|string|max:200',
            'services.*.description' => 'nullable|string|max:1000',
            'services.*.qty' => 'nullable|integer|min:1',
            'services.*.unit_price' => 'nullable|numeric|min:0',
            'components_client_total' => 'nullable|numeric|min:0',
        ]);

        $override = $data['components_client_total'] ?? null;
        $this->generator->saveItems(
            $quote,
            array_values($data['components'] ?? []),
            array_values($data['services'] ?? []),
            $override !== null ? (float) $override : null,
        );

        $step = $request->boolean('continue') ? 'document' : 'items';

        return redirect()->route('scope-planner.show', ['quote' => $quote, 'step' => $step])->with('success', __('portal.scope_planner.items_saved'));
    }

    /** Generate the document section prose (AI call 2), then reprice. */
    public function generate(Quote $quote)
    {
        $this->authorizeEditable($quote);

        if ($quote->items()->count() === 0) {
            return back()->withErrors(['items' => __('portal.scope_planner.confirm_items_first')]);
        }

        $this->generator->generate($quote);

        return redirect()->route('scope-planner.show', ['quote' => $quote, 'step' => 'document'])->with('success', __('portal.scope_planner.generated'));
    }

    /** Edit section text + the components override, then reprice. */
    public function update(Request $request, Quote $quote)
    {
        $this->authorizeEditable($quote);

        $data = $request->validate([
            'sections' => 'array',
            'array_sections' => 'array',
            'subject' => 'nullable|string|max:200',
            'beneficiary' => 'nullable|string|max:200',
            'components_client_total' => 'nullable|numeric|min:0',
        ]);

        // Rebuild ai_content from the edited section text: keys flagged in
        // array_sections become bullet arrays (split on newlines); the rest stay
        // strings. Untouched keys (timeline, scopes, …) are preserved.
        $content = $quote->ai_content ?? [];
        $arraySections = $data['array_sections'] ?? [];
        foreach (($data['sections'] ?? []) as $key => $val) {
            if (in_array($key, $arraySections, true)) {
                $content[$key] = collect(preg_split('/\r\n|\r|\n/', (string) $val))
                    ->map(fn ($l) => trim($l))->filter()->values()->all();
            } else {
                $content[$key] = trim((string) $val);
            }
        }

        $quote->update([
            'ai_content' => $content,
            'subject' => $data['subject'] ?? $quote->subject,
            'beneficiary' => $data['beneficiary'] ?? $quote->beneficiary,
            'components_client_total' => $data['components_client_total'] ?? $quote->components_client_total,
        ]);

        // Company per-scope edits (title + bullet lists).
        $toLines = fn ($v) => collect(preg_split('/\r\n|\r|\n/', (string) $v))
            ->map(fn ($l) => trim($l))->filter()->values()->all();
        foreach ((array) $request->input('scopes', []) as $scopeId => $fields) {
            $scope = $quote->scopes()->whereKey($scopeId)->first();
            if (! $scope) {
                continue;
            }
            $scope->update([
                'title' => trim((string) ($fields['title'] ?? '')) ?: $scope->title,
                'objective' => $toLines($fields['objective'] ?? ''),
                'inputs_required' => $toLines($fields['inputs_required'] ?? ''),
                'deliverables' => $toLines($fields['deliverables'] ?? ''),
                'acceptance_criteria' => $toLines($fields['acceptance_criteria'] ?? ''),
                'exclusions' => $toLines($fields['exclusions'] ?? ''),
            ]);
        }

        app(\App\Services\Scope\PricingService::class)->price($quote->refresh());

        return redirect()->route('scope-planner.show', ['quote' => $quote, 'step' => 'document'])->with('success', __('portal.scope_planner.saved'));
    }

    /** Regenerate a single section (replaces only that key in ai_content). */
    public function regenerateSection(Request $request, Quote $quote)
    {
        $this->authorizeEditable($quote);
        $section = (string) $request->validate(['section' => 'required|string|max:60'])['section'];

        $value = $this->gemini->regenerateSection([
            'brief' => (string) $quote->brief,
            'tier' => $quote->customer_category,
            'length' => $quote->length,
            'language' => $quote->language,
            'structure' => $quote->structure,
            'components' => $quote->items()->where('type', 'component')->pluck('name')->all(),
            'services' => $quote->items()->where('type', 'service')->pluck('name')->all(),
        ], $section);

        if ($value !== null) {
            $content = $quote->ai_content ?? [];
            $content[$section] = $value;
            $quote->update(['ai_content' => $content]);
        }

        return redirect()->route('scope-planner.show', ['quote' => $quote, 'step' => 'document'])->with('success', __('portal.scope_planner.section_regenerated'));
    }

    /** Lock the document for export (status → approved/pending per role). */
    public function finalize(Quote $quote)
    {
        $this->authorizeEditable($quote);

        $quote->update(['status' => Auth::user()->isManager() ? 'approved' : 'pending_approval']);

        return redirect()->route('scope-planner.show', $quote)->with('success', __('portal.scope_planner.finalized'));
    }

    /** Reopen a submitted/approved/changes-requested quote back to draft for editing. */
    public function reopen(Quote $quote)
    {
        $this->authorizeQuote($quote);

        // An already client-accepted quote is contractually committed — don't reopen.
        abort_if($quote->status === 'accepted', 403);

        // Reverting an approved or already-sent quote discards a manager's approval
        // (and pulls a sent quote from the client's view), so restrict that to
        // managers/PMs; a plain creator may only reopen pre-approval states.
        $user = Auth::user();
        abort_if(
            in_array($quote->status, ['approved', 'sent'], true) && ! ($user->isManager() || $user->isProjectManager()),
            403,
        );

        $quote->update(['status' => 'draft']);

        return redirect()->route('scope-planner.show', ['quote' => $quote, 'step' => 'document'])
            ->with('success', __('portal.scope_planner.reopened'));
    }

    /** Internal access guard: internal staff; only the creator or a manager may open a quote. */
    private function authorizeQuote(Quote $quote): void
    {
        $user = Auth::user();
        abort_unless($user && $user->isInternal(), 403);
        abort_unless($user->isManager() || $user->isProjectManager() || $quote->created_by === $user->id, 403);
    }

    /**
     * Authorize AND require the quote to be editable (draft / changes_requested).
     * The "locked" UI is not enough — these mutating endpoints must reject a direct
     * POST against a submitted/approved/sent/accepted quote, or an accepted contract
     * could be silently re-priced and re-itemized.
     */
    private function authorizeEditable(Quote $quote): void
    {
        $this->authorizeQuote($quote);
        abort_unless($quote->isEditable(), 403);
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
            return back()->withErrors(['items' => __('portal.scope_planner.select_item_first')])->withInput();
        }

        $opp = ! empty($validated['opportunity_id']) ? Opportunity::find($validated['opportunity_id']) : null;
        $qty = $request->input('qty', []);
        $stockQty = $request->input('stock_qty', []);
        $validity = (int) config('scope.validity_days', 30);

        $quote = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $category, $opp, $items, $stock, $qty, $stockQty, $validity) {
            $quote = Quote::create([
                'quote_number' => app(\App\Services\Scope\QuoteNumberGenerator::class)->next(),
                'title' => $validated['title'] ?? $validated['project_type'],
                'scope' => $validated['scope'] ?? null,
                'status' => 'draft',
                'customer_category' => $category,
                'language' => 'en',
                'vat_rate' => (float) config('scope.vat_rate', 15),
                'validity_days' => $validity,
                'valid_until' => now()->addDays($validity),
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

            return $quote;
        });

        return redirect()->route('quotes.show', $quote)->with('success', __('portal.scope_planner.quote_saved'));
    }

    public function index()
    {
        abort_unless(Auth::user()->isInternal(), 403);

        // The AI Scope Planner IS the 3-step wizard now — open it at Step 1 (Brief)
        // instead of the old single-page quick planner. Existing drafts are resumed
        // from the Quotes list ("Open in planner").
        return redirect()->route('scope-planner.create');
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
                'type' => 'component',
                'source' => 'inventory',
                'internal_cost' => $item->internal_cost,
                'markup_percentage' => $item->markup_percentage,
                'qty' => $q,
                'unit_price' => $q > 0 ? round($lineClient / $q, 2) : $lineClient,
                'line_internal' => $lineInternal,
                'line_client' => $lineClient,
                'is_client_visible' => false,
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
                'type' => 'component',
                'source' => 'inventory',
                'unit' => $item->unit,
                'internal_cost' => $purchase,
                'markup_percentage' => $markup,
                'qty' => $q,
                'unit_price' => $unit,
                'line_internal' => $lineInternal,
                'line_client' => $lineClient,
                'is_client_visible' => false,
            ],
            'preview' => [
                'name' => $item->name, 'category' => $cat,
                'cost' => $purchase, 'markup' => $markup,
                'qty' => $q, 'line_client' => $lineClient,
            ],
        ];
    }
}
