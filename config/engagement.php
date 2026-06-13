<?php

/**
 * VujaDe Engagement System configuration — the "Impact Points" rulebook.
 * Point values, level thresholds and badges per the Engagement framework doc.
 */
return [

    // Points awarded per action key.
    'actions' => [
        // Execution
        'task_completed_early'   => 50,   // before the deadline
        'task_completed_on_time' => 20,   // on the deadline day
        // Collaboration
        'solution_comment'       => 30,   // helpful comment on a colleague's task
        'peer_review'            => 40,   // reviewing a teammate's document
        // Client success
        'client_reply_fast'      => 25,   // replied to a client comment within 2h
        'client_five_star'       => 100,  // received a 5-star rating
        // Platform hygiene
        'daily_status_update'    => 5,    // updating task status (daily bonus)
        // Peer gratitude
        'thank_you_received'     => 50,
        // Weekly planner integration
        'weekly_plan_on_time'    => 20,
        'weekly_plan_late'       => -50,
        'weekly_plan_approved'   => 30,
    ],

    // Levels: name, inclusive point range, unlocked perk.
    'levels' => [
        ['name' => 'Contributor',     'min' => 0,    'max' => 500,        'perk' => null],
        ['name' => 'Team Player',     'min' => 501,  'max' => 2000,       'perk' => 'Mentor badge'],
        ['name' => 'Client Champion', 'min' => 2001, 'max' => 5000,       'perk' => 'Gold profile border'],
        ['name' => 'VujaDe Legend',   'min' => 5001, 'max' => PHP_INT_MAX,'perk' => 'Quarterly Excellence bonus'],
    ],

    // Special badges (icon = Font Awesome class).
    'badges' => [
        'firefighter' => ['label' => 'The Firefighter', 'icon' => 'fa-fire',   'desc' => 'Helped on a Red (high-priority) project'],
        'speedster'   => ['label' => 'The Speedster',   'icon' => 'fa-bolt',   'desc' => 'Fastest average client reply time this month'],
        'sage'        => ['label' => 'The Sage',        'icon' => 'fa-lightbulb', 'desc' => 'Most helpful solution comments'],
    ],

    // Peer "Thank You" tokens granted to each employee per month.
    'thank_you_monthly_quota' => 5,

    // A high scorer who stops interacting for this many days triggers a Burnout check-in.
    'burnout_inactive_days' => 7,
];
