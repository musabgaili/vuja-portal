<?php

namespace App\ScopePlanner\Adapters;

use App\Models\PricingRule;
use App\ScopePlanner\Contracts\PricingToolContract;
use App\ScopePlanner\DTO\ServiceRateDTO;
use Illuminate\Support\Collection;

/** Wraps the existing Pricing Tool (PricingRule) as the source of service rates. */
class PricingRuleAdapter implements PricingToolContract
{
    public function services(): Collection
    {
        return PricingRule::query()
            ->where('is_active', true)
            ->orderBy('item')
            ->orderBy('level')
            ->get()
            ->map(fn (PricingRule $r) => $this->toDto($r));
    }

    public function rateFor(string|int $key): ?ServiceRateDTO
    {
        $rule = PricingRule::find((int) $key);

        return $rule ? $this->toDto($rule) : null;
    }

    private function toDto(PricingRule $r): ServiceRateDTO
    {
        return new ServiceRateDTO(
            key: (string) $r->id,
            nameEn: $r->name_en ?: $r->item,
            nameAr: $r->name_ar,
            unit: $r->unit,
            unitRate: (float) $r->rate,
            defaultQty: 1.0,
            level: $r->level,
            description: $r->description,
        );
    }
}
