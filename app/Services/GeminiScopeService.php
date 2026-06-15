<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\PricingRule;
use App\Models\StockItem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AI Scope drafting (Google Gemini) for the Scope Planner.
 *
 * Two interactions used by the upgraded planner:
 *   suggest()  → inventory items + services from a brief (names/keys only)
 *   generate() → tier-specific document section prose as structured JSON
 *
 * HARD RULE: the AI returns prose/names only — never prices, quantities, totals,
 * VAT or milestone amounts (PricingService computes those). Everything falls
 * back to a deterministic, bilingual offline draft when no API key is set or a
 * call fails, so the feature is always usable.
 */
class GeminiScopeService
{
    // ===================================================================
    // Legacy single-shot draft (kept for the original planner screen).
    // ===================================================================

    /** @return array{scope:string, suggested:array<int,int>, source:string} */
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

    // ===================================================================
    // Call 1 — item + service suggestion from the brief.
    // ===================================================================

    /**
     * @param  array{brief:string,tier?:string,language?:string}  $req
     * @return array{components:array,services:array,source:string}
     */
    public function suggest(array $req): array
    {
        $brief = (string) ($req['brief'] ?? '');
        $tier = $req['tier'] ?? 'company';
        $language = $req['language'] ?? 'en';

        $key = config('services.gemini.key');
        if ($key && trim($brief) !== '') {
            try {
                return $this->geminiSuggest($key, $brief, $tier, $language);
            } catch (\Throwable $e) {
                Log::warning('Gemini suggest failed, using offline match: '.$e->getMessage());
            }
        }

        return $this->offlineSuggest($brief);
    }

    private function geminiSuggest(string $key, string $brief, string $tier, string $language): array
    {
        $system = 'You are a senior engineer at Vuja De Innovation. From a project brief, list the electronic/hardware '
            .'components likely required (as search queries against an inventory catalog). Output ONLY JSON. No prices.';
        $user = "Brief: {$brief}\nTier: {$tier}. Return components as catalog search queries.";
        $schema = [
            'type' => 'object',
            'properties' => [
                'components' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'qty' => ['type' => 'integer'],
                            'reason' => ['type' => 'string'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            'required' => ['components'],
        ];

        $json = $this->callGeminiJson($key, $system, $user, $schema);

        $components = collect($json['components'] ?? [])
            ->map(fn ($c) => ['query' => trim((string) ($c['query'] ?? '')), 'qty' => max(1, (int) ($c['qty'] ?? 1)), 'reason' => (string) ($c['reason'] ?? '')])
            ->filter(fn ($c) => $c['query'] !== '')
            ->values()->all();

        // Services resolve deterministically against the Pricing Tool (real ids).
        $offline = $this->offlineSuggest($brief);

        return [
            'components' => $components ?: $offline['components'],
            'services' => $offline['services'],
            'source' => 'gemini',
        ];
    }

    /** Keyword-match the catalog (components) + Pricing Tool (services). */
    private function offlineSuggest(string $brief): array
    {
        $hay = Str::lower($brief);

        $components = [];
        foreach (StockItem::where('is_active', true)->orderBy('name')->limit(300)->get() as $i) {
            foreach (preg_split('/\s+/', Str::lower($i->name)) as $word) {
                if (strlen($word) >= 4 && str_contains($hay, $word)) {
                    $components[] = ['query' => $i->name, 'qty' => 1, 'reason' => 'Matched the brief'];
                    break;
                }
            }
            if (count($components) >= 8) {
                break;
            }
        }

        $services = [];
        foreach (PricingRule::where('is_active', true)->get() as $r) {
            $name = $r->name_en ?: $r->item;
            foreach (preg_split('/\s+/', Str::lower((string) $name)) as $word) {
                if (strlen($word) >= 4 && str_contains($hay, $word)) {
                    $services[] = ['key' => (string) $r->id, 'name' => $name, 'qty' => 1, 'reason' => 'Matched the brief'];
                    break;
                }
            }
        }

        return ['components' => $components, 'services' => $services, 'source' => 'offline'];
    }

    // ===================================================================
    // Call 2 — section prose generation per tier.
    // ===================================================================

    /**
     * @param  array{brief:string,tier:string,length?:string,language?:string,structure?:string,components?:array,services?:array}  $req
     */
    public function generate(array $req): array
    {
        $key = config('services.gemini.key');
        if ($key && trim((string) ($req['brief'] ?? '')) !== '') {
            try {
                return $this->geminiGenerate($key, $req);
            } catch (\Throwable $e) {
                Log::warning('Gemini generate failed, using offline draft: '.$e->getMessage());
            }
        }

        return $this->offlineGenerate($req);
    }

    /** Regenerate a single section by regenerating the document and returning that key. */
    public function regenerateSection(array $req, string $sectionKey): mixed
    {
        return $this->generate($req)[$sectionKey] ?? null;
    }

    private function geminiGenerate(string $key, array $req): array
    {
        $tier = $req['tier'] ?? 'company';
        $language = $req['language'] ?? 'en';
        $length = $req['length'] ?? 'medium';
        $structure = $req['structure'] ?? 'single';
        $langName = $language === 'ar' ? 'Arabic (Modern Standard Arabic)' : 'English';
        $budget = config('scope.length_budgets.'.$length, '5-8 bullets per section');

        $system = "You are a senior technical proposal writer at Vuja De Innovation (engineering, design, innovation; Riyadh). "
            ."Write the requested sections for a {$tier} {$langName} Scope-of-Work.\n"
            ."RULES:\n- Output ONLY JSON valid against the provided schema. No prose outside JSON.\n"
            ."- Write all human-readable text in {$langName}.\n"
            ."- NEVER include prices, quantities, totals, taxes, or payment amounts — those are added by the system.\n"
            ."- Be specific to the brief and the confirmed components; avoid generic filler.\n"
            ."- Depth: {$length} → {$budget}.\n- Tone: professional, factual, client-facing.";

        $user = "Brief:\n{$req['brief']}\n\n"
            .'Confirmed components: '.(empty($req['components']) ? '(none)' : implode(', ', (array) $req['components']))."\n"
            .'Selected services: '.(empty($req['services']) ? '(none)' : implode(', ', (array) $req['services']))."\n"
            ."Tier: {$tier}. Length: {$length}. Structure: {$structure}.";

        if ($tier === 'company') {
            $user .= $structure === 'multi_scope'
                ? ' Return MULTIPLE scopes in "scopes".'
                : ' Return EXACTLY ONE scope in "scopes".';
        }

        $json = $this->callGeminiJson($key, $system, $user, $this->schemaFor($tier));

        if (! $this->validGenerated($tier, $json)) {
            throw new \RuntimeException('Generated JSON did not match the tier schema.');
        }

        return $json;
    }

    /** Per-tier response schema (spec §9.2). */
    private function schemaFor(string $tier): array
    {
        $strArr = ['type' => 'array', 'items' => ['type' => 'string']];

        return match ($tier) {
            'student' => [
                'type' => 'object',
                'properties' => [
                    'subject' => ['type' => 'string'],
                    'needs' => ['type' => 'string'],
                    'scope_of_work' => $strArr,
                    'out_of_scope' => $strArr,
                    'notes' => $strArr,
                ],
                'required' => ['scope_of_work', 'out_of_scope'],
            ],
            'entrepreneur' => [
                'type' => 'object',
                'properties' => [
                    'introduction' => ['type' => 'string'],
                    'proposed_scope' => $strArr,
                    'technical_specs' => $strArr,
                    'mechanical_specs' => $strArr,
                    'operational_logic' => $strArr,
                    'implementation_phases' => $strArr,
                    'timeline' => [
                        'type' => 'array',
                        'items' => ['type' => 'object', 'properties' => [
                            'phase' => ['type' => 'string'], 'duration' => ['type' => 'string'], 'notes' => ['type' => 'string'],
                        ]],
                    ],
                    'out_of_scope' => $strArr,
                    'notes' => $strArr,
                ],
                'required' => ['introduction', 'proposed_scope'],
            ],
            default => [ // company
                'type' => 'object',
                'properties' => [
                    'introduction_purpose' => ['type' => 'string'],
                    'scopes' => [
                        'type' => 'array',
                        'items' => ['type' => 'object', 'properties' => [
                            'title' => ['type' => 'string'],
                            'objective' => ['type' => 'string'],
                            'inputs_required' => $strArr,
                            'deliverables' => $strArr,
                            'acceptance_criteria' => $strArr,
                            'exclusions' => $strArr,
                        ], 'required' => ['title', 'objective', 'deliverables']],
                    ],
                    'timeline' => [
                        'type' => 'array',
                        'items' => ['type' => 'object', 'properties' => [
                            'phase' => ['type' => 'string'], 'duration' => ['type' => 'string'], 'notes' => ['type' => 'string'],
                        ]],
                    ],
                ],
                'required' => ['introduction_purpose', 'scopes'],
            ],
        };
    }

    private function validGenerated(string $tier, array $json): bool
    {
        return match ($tier) {
            'student' => isset($json['scope_of_work']) && is_array($json['scope_of_work']),
            'entrepreneur' => isset($json['introduction'], $json['proposed_scope']),
            default => isset($json['scopes']) && is_array($json['scopes']) && count($json['scopes']) > 0,
        };
    }

    /** Deterministic, bilingual section draft (no API needed). */
    private function offlineGenerate(array $req): array
    {
        $tier = $req['tier'] ?? 'company';
        $language = $req['language'] ?? 'en';
        $brief = (string) ($req['brief'] ?? '');
        $components = (array) ($req['components'] ?? []);
        $ar = $language === 'ar';

        $points = $this->briefPoints($brief);
        $compBullets = collect($components)->map(fn ($c) => $ar ? "تجهيز/تكامل: {$c}" : "Provision & integration: {$c}")->values()->all();
        $scopeBullets = array_values(array_filter(array_merge($points, $compBullets)));
        if (empty($scopeBullets)) {
            $scopeBullets = [$ar ? 'تنفيذ المتطلبات المذكورة في الموجز إلى مستوى احترافي.' : 'Deliver the requirements described in the brief to a professional standard.'];
        }

        $outOfScope = [$ar ? 'أي بنود غير مذكورة صراحةً في نطاق العمل أعلاه.' : 'Anything not explicitly listed in the scope of work above.'];
        $notes = [$ar ? 'تبدأ مدة التنفيذ من تاريخ استلام الدفعة الأولى.' : 'Execution starts from the date the advance payment is received.'];
        $intro = $ar
            ? 'يقدّم هذا المستند نطاق العمل المقترح بناءً على المتطلبات الواردة في الموجز.'
            : 'This document presents the proposed scope of work based on the requirements in the brief.';

        if ($tier === 'student') {
            return [
                'subject' => $ar ? 'تنفيذ المشروع المطلوب' : 'Delivery of the requested project',
                'needs' => $brief,
                'scope_of_work' => $scopeBullets,
                'out_of_scope' => $outOfScope,
                'notes' => $notes,
                'source' => 'offline',
            ];
        }

        if ($tier === 'entrepreneur') {
            return [
                'introduction' => $intro,
                'proposed_scope' => $scopeBullets,
                'technical_specs' => $compBullets ?: [$ar ? 'تُحدَّد المواصفات الفنية وفق اختيار المكوّنات المناسبة.' : 'Technical specifications per the appropriate component selection.'],
                'operational_logic' => [$ar ? 'يوضَّح سيناريو التشغيل بعد تثبيت النطاق.' : 'The operating scenario is detailed once the scope is fixed.'],
                'implementation_phases' => [$ar ? 'التحليل والتصميم' : 'Analysis & design', $ar ? 'التنفيذ والاختبار' : 'Implementation & testing', $ar ? 'التسليم والتدريب' : 'Handover & training'],
                'timeline' => [
                    ['phase' => $ar ? 'التصميم' : 'Design', 'duration' => $ar ? 'يُحدَّد' : 'TBD', 'notes' => ''],
                    ['phase' => $ar ? 'التنفيذ' : 'Implementation', 'duration' => $ar ? 'يُحدَّد' : 'TBD', 'notes' => ''],
                ],
                'out_of_scope' => $outOfScope,
                'notes' => $notes,
                'source' => 'offline',
            ];
        }

        // company
        return [
            'introduction_purpose' => $intro,
            'scopes' => [[
                'title' => $ar ? 'النطاق الرئيسي' : 'Primary Scope',
                'objective' => $ar ? 'تحقيق متطلبات المشروع كما وردت في الموجز.' : 'Achieve the project requirements as described in the brief.',
                'inputs_required' => [$ar ? 'مدخلات ومتطلبات العميل الكاملة.' : 'Complete client inputs and requirements.'],
                'deliverables' => $scopeBullets,
                'acceptance_criteria' => [$ar ? 'مطابقة المخرجات للمواصفات المتفق عليها.' : 'Deliverables conform to the agreed specifications.'],
                'exclusions' => $outOfScope,
            ]],
            'timeline' => [
                ['phase' => $ar ? 'الانطلاق' : 'Kickoff', 'duration' => $ar ? 'يُحدَّد' : 'TBD', 'notes' => ''],
                ['phase' => $ar ? 'التسليم' : 'Delivery', 'duration' => $ar ? 'يُحدَّد' : 'TBD', 'notes' => ''],
            ],
            'source' => 'offline',
        ];
    }

    /** Split a free-text brief into clean bullet points. */
    private function briefPoints(string $brief): array
    {
        return collect(preg_split('/[\n\r;•]+|\.\s+/', $brief))
            ->map(fn ($l) => trim($l))
            ->filter(fn ($l) => strlen($l) >= 4)
            ->map(fn ($l) => Str::ucfirst($l))
            ->take(12)
            ->values()->all();
    }

    // ===================================================================
    // Gemini transport
    // ===================================================================

    /** Structured-output Gemini call: returns the decoded JSON object. */
    private function callGeminiJson(string $key, string $system, string $user, array $schema): array
    {
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

        $resp = Http::timeout(30)->post($url, [
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents' => [['parts' => [['text' => $user]]]],
            'generationConfig' => [
                'temperature' => 0.3,
                'response_mime_type' => 'application/json',
                'response_schema' => $schema,
            ],
        ])->throw()->json();

        $text = data_get($resp, 'candidates.0.content.parts.0.text', '');
        $data = json_decode($text, true);

        if (! is_array($data)) {
            throw new \RuntimeException('Gemini returned non-JSON output.');
        }

        return $data;
    }

    // ===================================================================
    // Legacy helpers (used by draft()).
    // ===================================================================

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

        $labor = $items->where('category', 'Labor')->take(2);
        $suggested = $matched->merge($labor);
        if ($suggested->where('category', 'Electronics')->isEmpty()) {
            $suggested = $suggested->merge($items->where('category', 'Electronics')->take(2));
        }

        return $suggested->pluck('id')->unique()->values()->all();
    }
}
