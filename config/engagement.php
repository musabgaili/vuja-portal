<?php

/**
 * VujaDe Engagement System configuration — the "Impact Points" rulebook.
 * Point values, level thresholds and badges per the Engagement framework doc.
 */
return [

    // Points awarded per action key. These are DEFAULTS — a manager can override
    // any value at runtime via the Engagement Settings editor (stored in the
    // `settings` table, key `engagement.actions`). EngagementService merges the
    // overrides over these defaults, so a missing/blank override falls back here.
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
        // Direct staff tasks (manager-assigned) — a higher rank than ordinary
        // task completion to reward owning a discrete deliverable end-to-end.
        'staff_task_project'     => 60,   // tied to a specific project
        'staff_task_presale'    => 70,   // pre-sale / scoping work
        'staff_task_sales'       => 80,   // sales / revenue work
        'staff_task_management'  => 90,   // management / internal initiatives
        // Innovation — the HIGHEST award in the system, granted only when a
        // manager approves a portal-improvement idea (see ImprovementIdeaController).
        'portal_improvement_idea_approved' => 150,
    ],

    // Editor metadata: groups each action under a category and gives it a label
    // for the Engagement Settings UI. Keys must match `actions` above. An action
    // with no entry here still works — it just won't show in the grouped editor.
    'categories' => [
        'execution'    => 'Execution',
        'collaboration'=> 'Collaboration',
        'client'       => 'Client success',
        'hygiene'      => 'Platform hygiene',
        'gratitude'    => 'Peer gratitude',
        'planner'      => 'Weekly planner',
        'staff_tasks'  => 'Direct staff tasks',
        'innovation'   => 'Innovation & ideas',
    ],
    'action_meta' => [
        'task_completed_early'   => ['category' => 'execution',     'label' => 'Task completed early'],
        'task_completed_on_time' => ['category' => 'execution',     'label' => 'Task completed on time'],
        'solution_comment'       => ['category' => 'collaboration', 'label' => 'Helpful solution comment'],
        'peer_review'            => ['category' => 'collaboration', 'label' => 'Peer document review'],
        'client_reply_fast'      => ['category' => 'client',        'label' => 'Fast client reply (<2h)'],
        'client_five_star'       => ['category' => 'client',        'label' => '5-star client rating'],
        'daily_status_update'    => ['category' => 'hygiene',       'label' => 'Daily status update'],
        'thank_you_received'     => ['category' => 'gratitude',     'label' => 'Thank-You received'],
        'weekly_plan_on_time'    => ['category' => 'planner',       'label' => 'Weekly plan on time'],
        'weekly_plan_late'       => ['category' => 'planner',       'label' => 'Weekly plan late (penalty)'],
        'weekly_plan_approved'   => ['category' => 'planner',       'label' => 'Weekly plan approved'],
        'staff_task_project'     => ['category' => 'staff_tasks',   'label' => 'Direct task — Project'],
        'staff_task_presale'    => ['category' => 'staff_tasks',   'label' => 'Direct task — Pre-sale'],
        'staff_task_sales'       => ['category' => 'staff_tasks',   'label' => 'Direct task — Sales'],
        'staff_task_management'  => ['category' => 'staff_tasks',   'label' => 'Direct task — Management'],
        'portal_improvement_idea_approved' => ['category' => 'innovation', 'label' => 'Portal improvement idea approved'],
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
