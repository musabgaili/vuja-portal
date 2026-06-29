<?php

/*
|--------------------------------------------------------------------------
| Email notification types
|--------------------------------------------------------------------------
| Each key is a notification type that can also be delivered by email (the
| in-app bell always shows it). Employees toggle these per type on their
| Notification Settings page; `default` is the value when they haven't chosen.
| `audience` controls who sees the toggle: all | internal | manager.
*/

return [
    'types' => [
        'chat_mention' => [
            'label' => 'portal.notif_prefs.type.chat_mention',
            'default' => true,
            'audience' => 'internal',
        ],
        'chat_dm' => [
            'label' => 'portal.notif_prefs.type.chat_dm',
            'default' => false,   // off by default — DMs can be chatty
            'audience' => 'internal',
        ],
        'task_assigned' => [
            'label' => 'portal.notif_prefs.type.task_assigned',
            'default' => true,
            'audience' => 'internal',
        ],
        'weekly_plan_reviewed' => [
            'label' => 'portal.notif_prefs.type.weekly_plan_reviewed',
            'default' => true,
            'audience' => 'internal',
        ],
        'quote_reviewed' => [
            'label' => 'portal.notif_prefs.type.quote_reviewed',
            'default' => true,
            'audience' => 'internal',
        ],
        'project_proposal_reviewed' => [
            'label' => 'portal.notif_prefs.type.project_proposal_reviewed',
            'default' => true,
            'audience' => 'internal',
        ],
        'meeting_invitation' => [
            'label' => 'portal.notif_prefs.type.meeting_invitation',
            'default' => true,
            'audience' => 'internal',
        ],
        'meeting_booked' => [
            'label' => 'portal.notif_prefs.type.meeting_booked',
            'default' => true,
            'audience' => 'internal',
        ],
        'meeting_reminder' => [
            'label' => 'portal.notif_prefs.type.meeting_reminder',
            'default' => true,
            'audience' => 'internal',
        ],
        'task_done' => [
            'label' => 'portal.notif_prefs.type.task_done',
            'default' => true,
            'audience' => 'internal',
        ],
        'project_created' => [
            'label' => 'portal.notif_prefs.type.project_created',
            'default' => true,
            'audience' => 'internal',
        ],
        'milestone_set' => [
            'label' => 'portal.notif_prefs.type.milestone_set',
            'default' => true,
            'audience' => 'internal',
        ],
    ],
];
