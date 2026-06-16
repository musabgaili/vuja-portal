<?php

namespace App\Engagement\Adapters;

use App\Engagement\Contracts\QuoteDiscountBridge;
use App\Models\Quote;
use App\Services\Scope\PricingService;

/**
 * The discount lives on the quote (discount_percent / discount_cap_sar) and is
 * applied by PricingService, so the price stays deterministic and survives a
 * re-price. The discount reduces SERVICE lines only, never components (spec §9).
 */
class ScopeQuoteDiscountBridge implements QuoteDiscountBridge
{
    public function __construct(private PricingService $pricing) {}

    public function applyServiceDiscount(int $quoteId, float $percent, float $capSar): float
    {
        $quote = Quote::findOrFail($quoteId);
        $quote->forceFill([
            'discount_percent' => round($percent, 2),
            'discount_cap_sar' => round($capSar, 2),
        ])->save();

        $this->pricing->price($quote);

        return (float) $quote->fresh()->discount_amount;
    }

    public function removeDiscount(int $quoteId): void
    {
        $quote = Quote::findOrFail($quoteId);
        $quote->forceFill([
            'discount_percent' => 0,
            'discount_cap_sar' => null,
            'discount_amount' => 0,
        ])->save();

        $this->pricing->price($quote);
    }
}
