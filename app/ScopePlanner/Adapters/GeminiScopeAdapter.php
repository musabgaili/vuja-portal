<?php

namespace App\ScopePlanner\Adapters;

use App\Services\GeminiScopeService;
use App\ScopePlanner\Contracts\ScopeAiContract;

/** Wraps the existing GeminiScopeService (Gemini + offline fallback). */
class GeminiScopeAdapter implements ScopeAiContract
{
    public function __construct(private GeminiScopeService $gemini) {}

    public function suggest(array $req): array
    {
        return $this->gemini->suggest($req);
    }

    public function generate(array $req): array
    {
        return $this->gemini->generate($req);
    }

    public function regenerateSection(array $req, string $sectionKey): mixed
    {
        return $this->gemini->regenerateSection($req, $sectionKey);
    }
}
