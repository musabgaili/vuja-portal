<?php

namespace App\ScopePlanner\DTO;

/** A fixed service rate exposed to the Scope Planner (wraps a PricingRule). */
final class ServiceRateDTO
{
    public function __construct(
        public readonly string $key,
        public readonly string $nameEn,
        public readonly ?string $nameAr,
        public readonly ?string $unit,
        public readonly float $unitRate,
        public readonly float $defaultQty = 1.0,
        public readonly ?string $level = null,
        public readonly ?string $description = null,
    ) {}

    /** Localised display name for the BoQ. */
    public function name(string $language = 'en'): string
    {
        return $language === 'ar' ? ($this->nameAr ?: $this->nameEn) : $this->nameEn;
    }
}
