<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Meeting reminder lead time
    |--------------------------------------------------------------------------
    | How many minutes before a meeting's start time the single reminder email
    | goes out. The scheduler runs meetings:send-reminders every 15 minutes.
    */
    'reminder_lead_minutes' => env('MEETING_REMINDER_LEAD_MINUTES', 60),
];
