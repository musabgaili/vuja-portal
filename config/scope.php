<?php

return [
    // Branding
    'company_name' => 'Vuja De Innovation',
    'brand_color'  => '#1B565E',                 // sampled from the letterhead
    'footer'       => 'Vuja De innovations Est. CR: 2251504740 — Address: Riyadh - Airport Road',
    'letterhead'   => 'scope/letterhead.png',    // storage/app/public -> asset('storage/scope/letterhead.png')
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
    'milestones' => [
        'student' => [
            ['code' => 'M1', 'percentage' => 50, 'trigger' => 'On order confirmation (advance)'],
            ['code' => 'M2', 'percentage' => 50, 'trigger' => 'On delivery / completion'],
        ],
        'entrepreneur' => [
            ['code' => 'M1', 'percentage' => 50, 'trigger' => 'On order confirmation (advance)'],
            ['code' => 'M2', 'percentage' => 30, 'trigger' => 'On prototype / milestone review'],
            ['code' => 'M3', 'percentage' => 20, 'trigger' => 'On final delivery & acceptance'],
        ],
        'company' => [
            ['code' => 'M1', 'percentage' => 40, 'trigger' => 'On contract signature (advance)'],
            ['code' => 'M2', 'percentage' => 30, 'trigger' => 'On mid-project milestone'],
            ['code' => 'M3', 'percentage' => 30, 'trigger' => 'On final delivery & acceptance'],
        ],
    ],

    // Verbosity budgets passed to the AI prompt (bullets per section).
    'length_budgets' => [
        'short'  => '3-5 concise bullets per section',
        'medium' => '5-8 bullets per section',
        'long'   => '8-12 bullets per section with sub-points',
    ],

    // PDF engine: 'mpdf' (default, pure-PHP, Arabic-capable), 'browsershot' (needs Node+Chrome), or 'snappy'.
    'pdf_engine' => env('SCOPE_PDF_ENGINE', 'mpdf'),
];
