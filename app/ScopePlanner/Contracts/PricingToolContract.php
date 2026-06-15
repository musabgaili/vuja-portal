<?php

namespace App\ScopePlanner\Contracts;

use App\ScopePlanner\DTO\ServiceRateDTO;
use Illuminate\Support\Collection;

/** Bound to the existing Pricing Tool (PricingRule) — the source of service rates. */
interface PricingToolContract
{
    /** All active service rate definitions for the picker. @return Collection<int,ServiceRateDTO> */
    public function services(): Collection;

    public function rateFor(string|int $key): ?ServiceRateDTO;
}
