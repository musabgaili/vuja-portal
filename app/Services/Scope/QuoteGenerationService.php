<?php

namespace App\Services\Scope;

use App\Models\Quote;
use App\Models\StockItem;
use App\ScopePlanner\Contracts\InventoryContract;
use App\ScopePlanner\Contracts\PricingToolContract;
use App\ScopePlanner\Contracts\ScopeAiContract;
use Illuminate\Support\Carbon;

/**
 * Orchestrates the scope-quote flow (spec §10):
 *   createDraft → suggestItems (AI) → saveItems (confirm) → generate (AI) → price.
 * The AI only ever supplies prose/names; PricingService computes every number.
 */
class QuoteGenerationService
{
    public function __construct(
        private ScopeAiContract $ai,
        private InventoryContract $inventory,
        private PricingToolContract $pricing,
        private PricingService $pricingService,
        private QuoteNumberGenerator $numbers,
    ) {}

    /** Persist a draft quote from the create form and issue its Q-number. */
    public function createDraft(array $data, int $userId): Quote
    {
        $validity = (int) config('scope.validity_days', 30);

        return Quote::create([
            'quote_number' => $this->numbers->next(),
            'status' => 'draft',
            'created_by' => $userId,
            'customer_category' => $data['tier'] ?? 'company',
            'language' => $data['language'] ?? 'en',
            'length' => $data['length'] ?? 'medium',
            'structure' => $data['structure'] ?? 'single',
            'title' => $data['title'] ?? ($data['subject'] ?? 'Untitled scope'),
            'subject' => $data['subject'] ?? null,
            'beneficiary' => $data['beneficiary'] ?? null,
            'client_ref' => $data['client_ref'] ?? null,
            'brief' => $data['brief'] ?? null,
            'vat_rate' => (float) config('scope.vat_rate', 15),
            'validity_days' => $validity,
            'valid_until' => Carbon::now()->addDays($validity),
            'client_id' => $data['client_id'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'contact_id' => $data['contact_id'] ?? null,
            'opportunity_id' => $data['opportunity_id'] ?? null,
        ]);
    }

    /** AI call 1: suggest components/services, resolved against the real catalog + Pricing Tool. */
    public function suggestItems(Quote $quote): array
    {
        $raw = $this->ai->suggest([
            'brief' => (string) $quote->brief,
            'tier' => $quote->customer_category,
            'language' => $quote->language,
        ]);

        $components = [];
        foreach (($raw['components'] ?? []) as $c) {
            $query = (string) ($c['query'] ?? '');
            $hit = $query !== '' ? $this->inventory->search($query, 1)->first() : null;
            $components[] = [
                'stock_item_id' => $hit?->id,
                'name' => $hit->name ?? $query,
                'category' => $hit->category ?? null,
                'unit' => $hit->unit ?? null,
                'qty' => max(1, (int) ($c['qty'] ?? 1)),
                'reason' => trim((string) ($c['reason'] ?? '')).($hit ? '' : ' (no catalog match — add manually)'),
            ];
        }

        $services = [];
        foreach (($raw['services'] ?? []) as $s) {
            $rate = isset($s['key']) ? $this->pricing->rateFor($s['key']) : null;
            if ($rate) {
                $services[] = [
                    'pricing_rule_id' => $rate->key,
                    'name' => $rate->name($quote->language),
                    'unit' => $rate->unit,
                    'unit_rate' => $rate->unitRate,
                    'qty' => max(1, (int) ($s['qty'] ?? 1)),
                    'reason' => (string) ($s['reason'] ?? ''),
                ];
            }
        }

        return ['components' => $components, 'services' => $services, 'source' => $raw['source'] ?? 'offline'];
    }

    /**
     * Persist the employee-confirmed items then reprice.
     *
     * @param  array<int,array>  $components  each: stock_item_id?, name, category?, unit?, qty, internal_cost?
     * @param  array<int,array>  $services    each: pricing_rule_id?, name, unit?, qty, unit_price?
     */
    public function saveItems(Quote $quote, array $components, array $services, ?float $componentsClientOverride = null): Quote
    {
        $quote->items()->delete();
        $sort = 0;

        foreach ($components as $c) {
            $stock = ! empty($c['stock_item_id']) ? StockItem::find($c['stock_item_id']) : null;
            $quote->items()->create([
                'type' => 'component',
                'source' => $stock ? 'inventory' : 'manual',
                'stock_item_id' => $stock?->id,
                'name' => $c['name'] ?? ($stock->name ?? 'Component'),
                'category' => $c['category'] ?? ($stock->category ?? 'Electronics'),
                'unit' => $c['unit'] ?? ($stock->unit ?? null),
                'qty' => max(1, (int) ($c['qty'] ?? 1)),
                'internal_cost' => $stock ? (float) $stock->purchase_price : (float) ($c['internal_cost'] ?? 0),
                'unit_price' => 0,              // priced by PricingService at the tier price
                'is_client_visible' => false,   // rolled up into one client line
                'sort_order' => $sort++,
            ]);
        }

        foreach ($services as $s) {
            $rate = ! empty($s['pricing_rule_id']) ? $this->pricing->rateFor($s['pricing_rule_id']) : null;
            $quote->items()->create([
                'type' => 'service',
                'source' => $rate ? 'pricing_tool' : 'manual',
                'pricing_rule_id' => $rate ? (int) $rate->key : null,
                'name' => $s['name'] ?? ($rate?->name($quote->language) ?? 'Service'),
                'category' => 'Service',
                'unit' => $s['unit'] ?? ($rate->unit ?? null),
                'qty' => max(1, (int) ($s['qty'] ?? 1)),
                'unit_price' => isset($s['unit_price']) ? (float) $s['unit_price'] : (float) ($rate->unitRate ?? 0),
                'is_client_visible' => true,
                'sort_order' => $sort++,
            ]);
        }

        if ($componentsClientOverride !== null) {
            $quote->update(['components_client_total' => $componentsClientOverride]);
        }

        return $this->pricingService->price($quote->refresh());
    }

    /** AI call 2: generate section prose, persist it, build Company scopes, then reprice. */
    public function generate(Quote $quote): Quote
    {
        $quote->loadMissing('items');

        $content = $this->ai->generate([
            'brief' => (string) $quote->brief,
            'tier' => $quote->customer_category,
            'length' => $quote->length,
            'language' => $quote->language,
            'structure' => $quote->structure,
            'components' => $quote->items->where('type', 'component')->pluck('name')->all(),
            'services' => $quote->items->where('type', 'service')->pluck('name')->all(),
        ]);

        $quote->update(['ai_content' => $content]);

        if ($quote->customer_category === 'company') {
            $this->syncScopes($quote, $content['scopes'] ?? []);

            // Allocate the line items to a scope so per-scope pricing isn't zero.
            // Default: everything under the first scope (granular item↔scope
            // allocation is a future UI enhancement); single-scope is then exact.
            $first = $quote->scopes()->orderBy('sort_order')->first();
            if ($first) {
                $quote->items()->update(['quote_scope_id' => $first->id]);
            }
        }

        return $this->pricingService->price($quote->refresh());
    }

    /** Mirror ai_content.scopes into editable QuoteScope rows (Company multi-scope). */
    private function syncScopes(Quote $quote, array $scopes): void
    {
        $quote->scopes()->delete();

        foreach (array_values($scopes) as $i => $s) {
            $quote->scopes()->create([
                'sort_order' => $i,
                'title' => $s['title'] ?? ('Scope '.($i + 1)),
                'type' => 'commercial',
                'objective' => isset($s['objective']) ? [(string) $s['objective']] : [],
                'inputs_required' => $s['inputs_required'] ?? [],
                'deliverables' => $s['deliverables'] ?? [],
                'acceptance_criteria' => $s['acceptance_criteria'] ?? [],
                'exclusions' => $s['exclusions'] ?? [],
                'price' => 0,
            ]);
        }
    }
}
