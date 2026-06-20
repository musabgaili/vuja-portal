<?php

return [
    // Branding
    'company_name' => 'Vuja De Innovation',
    'brand_color'  => '#1B565E',                 // sampled from the letterhead
    'footer'       => 'Vuja De innovations Est. CR: 2251504740 — Address: Riyadh - Airport Road',
    // Letterhead path is relative to public/ so it is git-tracked and ships with
    // the repo (no storage:link / storage/app/public dependency). Used as both a
    // URL via asset() (online viewer) and a file via public_path() (mPDF watermark).
    'letterhead'   => 'images/scope-letterhead.png',
    'mirror_rtl_letterhead' => false,            // true once a mirrored letterhead-ar.png is supplied

    // Page geometry (US Letter) + measured safe area (mm)
    'page_size'    => 'letter',
    'safe_area_mm' => ['top' => 40, 'side' => 18, 'bottom' => 24],

    // Commercial
    'currency'      => 'SAR',
    'vat_rate'      => 15.00,
    'validity_days' => 30,

    // Quote numbering — Q-series. {seq4} zero-padded, {seq} raw, {year}.
    'number_format' => 'Q{seq4}',

    // Default milestone templates per tier (editable per quote). Amounts derive from grand_total.
    // Triggers are translation keys (scope.mtrigger.*) resolved in the quote's
    // language at render time; employee-edited free text renders verbatim.
    'milestones' => [
        'student' => [
            ['code' => 'M1', 'percentage' => 50, 'trigger' => 'scope.mtrigger.on_order_advance'],
            ['code' => 'M2', 'percentage' => 50, 'trigger' => 'scope.mtrigger.on_delivery'],
        ],
        'entrepreneur' => [
            ['code' => 'M1', 'percentage' => 50, 'trigger' => 'scope.mtrigger.on_order_advance'],
            ['code' => 'M2', 'percentage' => 30, 'trigger' => 'scope.mtrigger.on_prototype_review'],
            ['code' => 'M3', 'percentage' => 20, 'trigger' => 'scope.mtrigger.on_final_acceptance'],
        ],
        'company' => [
            ['code' => 'M1', 'percentage' => 40, 'trigger' => 'scope.mtrigger.on_contract_signature'],
            ['code' => 'M2', 'percentage' => 30, 'trigger' => 'scope.mtrigger.on_mid_milestone'],
            ['code' => 'M3', 'percentage' => 30, 'trigger' => 'scope.mtrigger.on_final_acceptance'],
        ],
    ],

    // Verbosity budgets passed to the AI prompt (bullets per section).
    'length_budgets' => [
        'short'  => '3-5 concise bullets per section',
        'medium' => '5-8 bullets per section',
        'long'   => '8-12 bullets per section with sub-points',
    ],

    // Canonical document sections per tier (key => string|array). Single source of
    // truth shared by the Step-3 editor fields and the AI prompt's section list,
    // so the generated text matches the sections the document renders. The label
    // for each key is scope.<key> in lang/{en,ar}/scope.php. (Company scopes &
    // timeline are produced via the response schema, not these flat sections.)
    'sections' => [
        'student' => [
            'needs' => 'string', 'scope_of_work' => 'array', 'out_of_scope' => 'array', 'notes' => 'array',
        ],
        'entrepreneur' => [
            'introduction' => 'string', 'proposed_scope' => 'array', 'technical_specs' => 'array',
            'mechanical_specs' => 'array', 'operational_logic' => 'array', 'implementation_phases' => 'array',
            'out_of_scope' => 'array', 'notes' => 'array',
        ],
        'company' => [
            'introduction_purpose' => 'string', 'out_of_scope' => 'array', 'notes' => 'array',
        ],
    ],

    // Manager-editable AI prompt templates (defaults). A row in scope_prompt_settings
    // overrides any of these at runtime; the response schema + JSON validation stay
    // in code, so an edited prompt can never break structured generation.
    // Placeholders: {tier} {lang} {length} {budget} {brief} {components} {services}
    // {structure} {sections} {company_scope_rule}
    'prompts' => [
        'generate_system' =>
            "You are a senior technical proposal writer at Vuja De Innovation (engineering, design, innovation; Riyadh). "
            ."Write the requested sections for a {tier} {lang} Scope-of-Work.\n"
            ."RULES:\n- Output ONLY JSON valid against the provided schema. No prose outside JSON.\n"
            ."- Write all human-readable text in {lang}.\n"
            ."- NEVER include prices, quantities, totals, taxes, or payment amounts — those are added by the system.\n"
            ."- Be specific to the brief and the confirmed components; avoid generic filler.\n"
            ."- Depth: {length} → {budget}.\n- Tone: professional, factual, client-facing.",
        'generate_user' =>
            "Brief:\n{brief}\n\n"
            ."Confirmed components: {components}\n"
            ."Selected services: {services}\n"
            ."Tier: {tier}. Length: {length}. Structure: {structure}.\n"
            ."Fill EVERY one of these sections in the JSON: {sections}.{company_scope_rule}",
        'suggest_system' =>
            'You are a senior engineer at Vuja De Innovation. From a project brief, list the electronic/hardware '
            .'components likely required (as search queries against an inventory catalog). Output ONLY JSON. No prices.',
    ],

    // PDF engine: 'mpdf' (default, pure-PHP) or 'browsershot' (headless Chrome —
    // matches the on-screen preview incl. Arabic, needs Node + Chromium on the server).
    'pdf_engine' => env('SCOPE_PDF_ENGINE', 'mpdf'),

    // Browsershot binary overrides (only needed if not auto-detected on PATH).
    'node_binary'  => env('BROWSERSHOT_NODE_BINARY'),
    'npm_binary'   => env('BROWSERSHOT_NPM_BINARY'),
    'chrome_path'  => env('BROWSERSHOT_CHROME_PATH'),
    'browsershot_no_sandbox' => (bool) env('BROWSERSHOT_NO_SANDBOX', true),
];
