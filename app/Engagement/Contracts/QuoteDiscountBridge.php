<?php

namespace App\Engagement\Contracts;

/** Applies a service-discount voucher to a Scope Planner quote. Spec §5, §9. */
interface QuoteDiscountBridge
{
    /**
     * Reduce the quote's SERVICE line items only (never the Electronic Components
     * line), capped in SAR. Returns the SAR discount actually applied.
     */
    public function applyServiceDiscount(int $quoteId, float $percent, float $capSar): float;

    /** Clear any voucher discount and re-price the quote (e.g. on cancel). */
    public function removeDiscount(int $quoteId): void;
}
