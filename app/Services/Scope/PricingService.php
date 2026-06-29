<?php

namespace App\Services\Scope;

use App\Models\Quote;
use App\ScopePlanner\Contracts\InventoryContract;
use App\ScopePlanner\Contracts\PricingToolContract;

/**
 * The single source of pricing truth for a scope quote (spec §11.1). The AI
 * never authors a number — every figure here is computed deterministically from
 * the Inventory tier prices and the Pricing Tool service rates.
 *
 *   components (tier price) → components_internal_total
 *   client components       = employee override ?? internal sum   (margin hidden)
 *   services (fixed rate)   → summed
 *   subtotal                = client components + services
 *   vat_amount              = round(subtotal * vat_rate / 100, 2)
 *   grand_total             = subtotal + vat
 *   milestones              = grand_total split by the per-tier % template
 */
class PricingService
{
    public function __construct(
        private InventoryContract $inventory,
        private PricingToolContract $pricing,
    ) {}

    public function price(Quote $quote): Quote
    {
        $quote->loadMissing('items');
        $tier = $quote->customer_category ?: 'company';

        // --- Components (inventory, tier-priced; rolled up for the client) ---
        $componentsInternalTotal = 0.0;   // itemised sum at TIER price
        $internalCost = 0.0;              // purchase cost (margin tracking)

        foreach ($quote->items->where('type', 'component') as $c) {
            $unit = ($c->source === 'inventory' && $c->stock_item_id)
                ? $this->inventory->priceFor((int) $c->stock_item_id, $tier)
                : (float) $c->unit_price;

            $qty = max(1, (int) $c->qty);
            $line = round($unit * $qty, 2);
            $cost = round((float) $c->internal_cost * $qty, 2);

            $c->forceFill([
                'unit_price' => $unit,
                'line_client' => $line,
                'line_internal' => $cost,
                'is_client_visible' => false,
            ])->save();

            $componentsInternalTotal += $line;
            $internalCost += $cost;
        }

        $componentsClientTotal = $quote->components_client_total !== null
            ? (float) $quote->components_client_total
            : $componentsInternalTotal;

        // --- Services (fixed Pricing Tool rate; itemised to the client) ---
        $servicesTotal = 0.0;
        foreach ($quote->items->whereIn('type', ['service', 'other']) as $s) {
            $unit = (float) $s->unit_price;
            if ($unit <= 0 && $s->pricing_rule_id) {
                $unit = (float) ($this->pricing->rateFor($s->pricing_rule_id)?->unitRate ?? 0);
            }

            $qty = max(1, (int) $s->qty);
            $line = round($unit * $qty, 2);

            $s->forceFill([
                'unit_price' => $unit,
                'line_client' => $line,
                'is_client_visible' => true,
            ])->save();

            $servicesTotal += $line;
        }

        // --- Service-discount voucher (engagement reward): SERVICE lines only, capped (spec §9) ---
        // Hard-clamp the percent to the contractual maximum (15%) regardless of how
        // it reached the quote — margin protection that holds even if a bad value slips in.
        $maxPct = (float) config('engagement_points.discount_percent_max', 15);
        $discountPercent = min((float) ($quote->discount_percent ?? 0), $maxPct);
        $discountAmount = 0.0;
        if ($discountPercent > 0 && $servicesTotal > 0) {
            $raw = round($servicesTotal * $discountPercent / 100, 2);
            $cap = $quote->discount_cap_sar !== null ? (float) $quote->discount_cap_sar : $raw;
            $discountAmount = round(min($raw, $cap), 2);
        }

        // --- Totals + 15% VAT (discount applied before VAT) ---
        $subtotal = round($componentsClientTotal + $servicesTotal - $discountAmount, 2);
        $vatRate = (float) ($quote->vat_rate ?? 15);
        $vatAmount = round($subtotal * $vatRate / 100, 2);
        $grandTotal = round($subtotal + $vatAmount, 2);

        $quote->forceFill([
            'components_internal_total' => round($componentsInternalTotal, 2),
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'grand_total' => $grandTotal,
            'total_internal' => round($internalCost, 2),
            'total_client' => $grandTotal,
        ])->save();

        $this->buildMilestones($quote, $grandTotal);
        $this->repriceScopes($quote);

        return $quote->refresh();
    }

    /**
     * Compute milestone AMOUNTS from the grand total (last absorbs the rounding
     * remainder so they sum exactly). The milestone STRUCTURE — codes, triggers,
     * percentages, order — is preserved, so employee edits survive every reprice.
     * The per-tier template only SEEDS the schedule when a quote has none yet.
     */
    private function buildMilestones(Quote $quote, float $grandTotal): void
    {
        $existing = $quote->milestones()->orderBy('sort_order')->get();

        if ($existing->isEmpty()) {
            $tier = $quote->customer_category ?: 'company';
            $template = array_values(config('scope.milestones.'.$tier, config('scope.milestones.company', [])));
            foreach ($template as $i => $m) {
                $quote->milestones()->create([
                    'code' => $m['code'],
                    'trigger' => $m['trigger'] ?? null,
                    'percentage' => (float) $m['percentage'],
                    'amount' => 0,
                    'sort_order' => $i,
                ]);
            }
            $existing = $quote->milestones()->orderBy('sort_order')->get();
        }

        if ($existing->isEmpty()) {
            return;
        }

        $last = $existing->count() - 1;
        $running = 0.0;
        foreach ($existing->values() as $i => $m) {
            $pct = (float) $m->percentage;
            $amount = $i === $last ? round($grandTotal - $running, 2) : round($grandTotal * $pct / 100, 2);
            if ($i !== $last) {
                $running += $amount;
            }
            $m->forceFill(['amount' => $amount])->save();
        }
    }

    /** Each Company scope's price = sum of its own line items (client-facing). */
    private function repriceScopes(Quote $quote): void
    {
        $quote->loadMissing('scopes', 'items');
        foreach ($quote->scopes as $scope) {
            $price = $quote->items
                ->where('quote_scope_id', $scope->id)
                ->sum(fn ($it) => (float) $it->line_client);
            $scope->forceFill(['price' => round($price, 2)])->save();
        }
    }
}
