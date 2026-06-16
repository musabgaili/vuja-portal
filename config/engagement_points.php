<?php

/*
|--------------------------------------------------------------------------
| Client Engagement Points program (spec §16)
|--------------------------------------------------------------------------
| Distinct from config/engagement.php (internal-staff Impact Points). The
| values below seed the DB tables (earning_rules / redemption_options / tiers)
| and are editable from the admin module thereafter; only the operational
| knobs here are read at runtime.
*/

return [
    'expiry_months' => 24,                 // earned points expire after 24 months (FIFO)
    'currency' => 'SAR',

    // Referral share link base: <base>/<code>
    'referral_base_url' => env('ENGAGEMENT_REFERRAL_URL', rtrim(env('APP_URL', 'http://localhost'), '/').'/r'),
    'referral_value_threshold' => 20000,   // project value < => small reward, >= => large reward
    'referral_min_qualifying' => 1000,     // min project value to count as a qualifying referral

    'discount_cap_sar' => 2500,            // default per-redemption SAR cap on service discounts
    'discount_percent_max' => 15,          // hard ceiling on a service discount (spec §3/§9: 15% max)
    'voucher_validity_days' => 180,        // issued voucher lifetime
    'claim_recency_days' => 30,            // a claimed post must be within this many days (spec §11)

    'welcome_points' => 25,                // starter points for a referred client (two-sided perk)

    // Two-step discretionary grants (spec §13)
    'roles' => [
        'grant_suggest' => 'project_manager',
        'grant_approve' => 'manager',
    ],

    // Rules a client may submit a manual claim for (social/reviews/attendance). Spec §11.
    'claimable_rules' => [
        'social_follow', 'social_story', 'social_post', 'review_public', 'event_attendance',
    ],
];
