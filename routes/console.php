<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly Planner: enforce the Saturday 18:00 deadline (run at 18:01).
Schedule::command('planner:mark-overdue')->saturdays()->at('18:01');

// Engagement Points: expire earns past their 24-month window (FIFO).
Schedule::command('engagement:expire-points')->dailyAt('02:00');

// Performance Targets: snapshot the just-ended month into the trend log (safety net).
Schedule::command('targets:snapshot-month')->monthlyOn(1, '00:30');
