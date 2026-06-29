<?php

/*
|--------------------------------------------------------------------------
| Employee Performance Targets & Capacity
|--------------------------------------------------------------------------
| Phase 1 = auto-measured monthly targets + progress + monthly log + 6-month
| trend + the points gate. Phase 2 will add the capacity/allocation layer; its
| defaults live here too so they're ready to seed.
*/

return [
    'currency' => 'SAR',
    'trend_months' => 6,                 // how many months the trend chart shows

    // Who holds targets vs who manages them.
    'roles' => [
        'holder' => ['employee', 'project_manager'],
        'manager' => 'manager',
    ],

    // RAG thresholds on attainment % (actual ÷ target).
    'rag' => [
        'green' => 100,   // >= 100% of target
        'amber' => 70,    // >= 70% and < 100%
        // below amber => red
    ],

    // ---- Phase 2 (capacity layer) defaults — seeded later, editable in UI ----
    'default_weekly_capacity' => 40,
    'workday_hours' => 8,
    'default_utilization_pct' => 70,
    'default_split' => ['delivery' => 70, 'pre_sales' => 20, 'internal' => 10],

    // Team member specialisations (machine values). Labels live in lang
    // targets.cap.spec.*; the manager picks one per engineer.
    'specializations' => ['management', 'programmer', 'designer', 'marketing', '3d_designer', 'electronics'],
];
