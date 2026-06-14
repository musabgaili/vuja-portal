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

    // Submission deadline: Saturday 18:00 for the upcoming week.
    'deadline_day' => 'Saturday',
    'deadline_time' => '18:00',
];
