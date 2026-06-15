<?php

namespace App\ScopePlanner\Contracts;

/**
 * Bound to the existing Gemini client. The AI returns names/prose ONLY — never
 * prices, quantities, totals or milestone amounts (those are computed by
 * PricingService). All methods take/return plain arrays for simplicity.
 */
interface ScopeAiContract
{
    /**
     * Suggest inventory items + services from a brief.
     * @param  array{brief:string,tier:string,language:string}  $req
     * @return array{components:array<int,array{query:string,qty:int,reason?:string}>,services:array<int,array{key:string,qty:int,reason?:string}>}
     */
    public function suggest(array $req): array;

    /**
     * Generate document section prose as structured JSON per the tier schema.
     * @param  array{brief:string,tier:string,length:string,language:string,structure?:string,components:array<int,string>,services:array<int,string>}  $req
     * @return array  validated section content (no numbers)
     */
    public function generate(array $req): array;

    /** Regenerate a single section; returns that section's value. */
    public function regenerateSection(array $req, string $sectionKey): mixed;
}
