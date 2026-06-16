<?php

return [

    // Master switch for auto-translating operational content (titles/descriptions
    // of projects, milestones, tasks, and service requests).
    'enabled' => env('TRANSLATIONS_ENABLED', true),

    // false = translate synchronously on save (works with no queue worker).
    // true  = dispatch to the queue (set up `php artisan queue:work`).
    'queue' => env('TRANSLATIONS_QUEUE', false),

];
