<?php

/**
 * CRM pipeline configuration.
 */
return [
    // Open pipeline stages (shown as Kanban columns), in order.
    'stages' => [
        'new'         => 'New',
        'qualified'   => 'Qualified',
        'proposition' => 'Proposition',
    ],

    // Terminal outcomes.
    'won_stage' => 'won',
    'lost_stage' => 'lost',

    // Lead sources.
    'sources' => ['Website', 'Referral', 'Event', 'Cold outreach', 'Existing client', 'Other'],
];
