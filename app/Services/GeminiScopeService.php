<?php

namespace App\Services;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AI Scope drafting (Google Gemini) + inventory suggestion for the Pricing Tool.
 * Falls back to a structured offline draft when no API key is configured or the
 * call fails, so the feature is always usable.
 */
class GeminiScopeService
{
    /**
     * @return array{scope:string, suggested:array<int,int>, source:string}
     */
    public function draft(string $projectType, string $requirements, ?string $budget = null): array
    {
        $key = config('services.gemini.key');
        if ($key) {
            try {
                return $this->callGemini($key, $projectType, $requirements, $budget);
            } catch (\Throwable $e) {
                Log::warning('Gemini scope draft failed, using offline draft: '.$e->getMessage());
            }
        }

        return $this->offlineDraft($projectType, $requirements, $budget);
    }

    private function prompt(string $projectType, string $requirements, ?string $budget): string
    {
        return "Act as a technical project manager at VujaDe. Based on the requirements [{$requirements}] for a {$projectType}"
            .($budget ? " with a budget around {$budget}" : '')
            .", write a professional scope of work including Objectives, Deliverables, and Technical Specifications. "
            .'Also suggest a list of electronic/hardware components from a standard inventory for this type of project.';
    }

    private function callGemini(string $key, string $projectType, string $requirements, ?string $budget): array
    {
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

        $resp = Http::timeout(20)->post($url, [
            'contents' => [['parts' => [['text' => $this->prompt($projectType, $requirements, $budget)]]]],
        ])->throw()->json();

        $text = data_get($resp, 'candidates.0.content.parts.0.text', '');

        return [
            'scope' => $text ?: $this->offlineDraft($projectType, $requirements, $budget)['scope'],
            'suggested' => $this->suggestItems($projectType, $requirements),
            'source' => 'gemini',
        ];
    }

    /** A clean, professional scope template built from the inputs (no API needed). */
    private function offlineDraft(string $projectType, string $requirements, ?string $budget): array
    {
        $reqLines = collect(preg_split('/[\n,;•]+/', $requirements))
            ->map(fn ($l) => trim($l))->filter()->values();

        $deliverables = $reqLines->isEmpty()
            ? ['A working '.$projectType, 'Documentation & handover']
            : $reqLines->map(fn ($r) => Str::ucfirst($r))->all();

        $scope = "## Objectives\n"
            ."Deliver a {$projectType} that meets the client's stated requirements"
            .($budget ? " within the {$budget} budget" : '').", to a professional production standard.\n\n"
            ."## Deliverables\n"
            .collect($deliverables)->map(fn ($d) => "- {$d}")->implode("\n")."\n\n"
            ."## Technical Specifications\n"
            ."- Architecture and component selection appropriate to a {$projectType}.\n"
            ."- Integration, testing and quality assurance.\n"
            ."- Deployment, documentation and a handover/training session.\n\n"
            ."_Draft generated offline — connect a Gemini API key for AI-authored scopes._";

        return [
            'scope' => $scope,
            'suggested' => $this->suggestItems($projectType, $requirements),
            'source' => 'offline',
        ];
    }

    /** Suggest inventory item ids by matching keywords; else a sensible default mix. */
    private function suggestItems(string $projectType, string $requirements): array
    {
        $hay = Str::lower($projectType.' '.$requirements);
        $items = InventoryItem::where('active', true)->get();

        $matched = $items->filter(function (InventoryItem $i) use ($hay) {
            foreach (preg_split('/\s+/', Str::lower($i->name)) as $word) {
                if (strlen($word) >= 4 && str_contains($hay, $word)) {
                    return true;
                }
            }

            return false;
        });

        // Always include core labor; top up with a couple of electronics if nothing matched.
        $labor = $items->where('category', 'Labor')->take(2);
        $suggested = $matched->merge($labor);
        if ($suggested->where('category', 'Electronics')->isEmpty()) {
            $suggested = $suggested->merge($items->where('category', 'Electronics')->take(2));
        }

        return $suggested->pluck('id')->unique()->values()->all();
    }
}
