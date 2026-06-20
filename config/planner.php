<?php

/**
 * Weekly Strategic Planner configuration.
 */
return [

    // Mandatory total per week.
    'required_hours' => 40,

    // The three allocation buckets (key => label).
    'buckets' => [
        'projects'    => 'Allocated Projects',
        'development' => 'Self-Development',
        'presale'     => 'Pre-sale Activity',
    ],

    // Working days that need a location (Sun–Thu).
    'days' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'],

    // Selectable working locations (key => label).
    'locations' => [
        'home'          => 'Home',
        'malas_lab'     => 'Malas Lab',
        'noura_lab'     => 'Noura Lab',
        'monshaat_lab'  => 'Monshaat Lab',
    ],

    // Fixed non-project activities an employee can log time against. The employee
    // can ONLY pick from this list (plus their assigned projects/tasks) — no free
    // text. Shown as the "Administration" section of the weekly timesheet.
    'activities' => [
        'vacation'         => 'Vacation',
        'unpaid_vacation'  => 'Unpaid Vacation',
        'sick_time'        => 'Sick time',
        'quality_control'  => 'Quality Control',
        'presale_meetings' => 'Pre-sale Meetings',
        'presales'         => 'Presales',
        'marketing'        => 'Marketing',
        'administrative'   => 'Administrative',
        'non_project'      => 'Non Project Activity',
    ],

    // "Pre-sale Meetings" is a must-have: every submitted week needs at least this
    // many hours logged against it. Weeks that are mostly leave (see leave_activities)
    // are exempt so PTO isn't blocked. Tracked monthly by the presale_meeting_hours
    // target metric.
    'min_presale_meeting_hours' => 5,

    // Activities treated as leave for the pre-sale-meetings exemption.
    'leave_activities' => ['vacation', 'unpaid_vacation', 'sick_time'],

    // Max hours allowed in a single day cell (sanity cap for inputs).
    'max_hours_per_day' => 24,

    // Submission deadline: Saturday 18:00 for the upcoming week.
    'deadline_day' => 'Saturday',
    'deadline_time' => '18:00',
];
